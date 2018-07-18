<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\Entity\ExchangeRate;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ExRatesService
 * @package App\Service
 */
class ExRatesService {

    private $from = null;
    private $to = null;
    private $cbrExchangeRate = null;
    private $rbcExchangeRate = null;
    private $entityManager;
    private $serializer;

    /**
     * ExRatesService constructor.
     * @param string $from
     * @param string $to
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     */
    public function __construct(string $from,
                                string $to,
                                EntityManagerInterface $entityManager,
                                SerializerInterface $serializer)
    {
        if ($from === $to) {
            throw new Exception('There is no point in finding ratio of the same currency, it will always be 1!');
        }

        $this->from = $from;
        $this->to = $to;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /**
     * @param int $attempts
     * @return $this
     */
    public function fetchData($attempts = 10){

        $cbrObj = new CbrSDK($this->from, $this->to);
        $rbcObj = new RbcSDK($this->from, $this->to);

        $this->cbrExchangeRate = $cbrObj->fetch($this->from, $this->to, $attempts);
        $this->rbcExchangeRate = $rbcObj->fetch($this->from, $this->to, $attempts);

        return $this;
    }

    /**
     * @return float|int
     */
    public function getAverage(){
        return number_format(($this->rbcExchangeRate + $this->cbrExchangeRate) / 2, 4);
    }

    /**
     * @return float
     */
    public function getCbrExchangeRate(){
        return $this->cbrExchangeRate;
    }

    /**
     * @return float
     */
    public function getRbcExchangeRate(){
        return $this->rbcExchangeRate;
    }

    /**
     *
     */
    public function sendTodaysRatesToDB(){
        $exchangeRate = new ExchangeRate();
        $exchangeRate->setFromCurrency($this->from);
        $exchangeRate->setToCurrency($this->to);
        $exchangeRate->setValue($this->getAverage());
        $exchangeRate->setDate(date("d-m-Y"));

        $this->entityManager->persist($exchangeRate);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * @param $date
     * @return bool|float|int|string
     */
    public function fetch($date){
        $repository = $this->entityManager->getRepository(ExchangeRate::class);
        $item = $repository->findOneBy(['date' => $date]);
        $jsonContent = $this->serializer->serialize($item, 'json');
        return $jsonContent;
    }
}
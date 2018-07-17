<?php
namespace App\Service;

use Doctrine\ORM\EntityManager;
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
     * @param EntityManager $entityManager
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
     */
    public function fetchData($attempts = 10){

        $xmlFrom = null;
        $xmlTo = null;

        // request urls
        $urlToJson = 'https://cash.rbc.ru/cash/json/converter_currency_rate/?currency_from=' . $this->from . '&currency_to=' . $this->to . '&source=cbrf&sum=1&date=';
        $urlToXml = 'http://www.cbr.ru/scripts/XML_daily_eng.asp?date_req=' . date("d/m/Y");

        // get currency value from the xml
        // making $attempts of attempts to get the xml in case it is not received on the first request
        for ($i = 0; $i < $attempts; $i++){
            if ($tmpXml = file_get_contents($urlToXml)){
                $tmpXml = simplexml_load_string($tmpXml);

                // http://www.cbr.ru compares all currencies to RUR by default and does not contain RUR currency in the provided xml. That's why if $this->from or $this->to is RUR they are set to 1
                if($this->from != 'RUR'){
                    $xmlFrom = $tmpXml->xpath('/ValCurs/Valute/CharCode[.="' . $this->from . '"]/following-sibling::Value');
                    $xmlFrom = $xmlFrom[0][0];
                } else {
                    $xmlFrom = 1;
                }
                if($this->to != 'RUR'){
                    $xmlTo = $tmpXml->xpath('/ValCurs/Valute/CharCode[.="' . $this->to . '"]/following-sibling::Value');
                    $xmlTo = $xmlTo[0][0];
                } else {
                    $xmlTo = 1;
                }

                // convert string value from the xml file into a float with 4 decimal points
                $this->cbrExchangeRate = number_format($xmlFrom / $xmlTo, 4);
                $this->cbrExchangeRate = floatval($this->cbrExchangeRate);
                break;
            }
        }
        // $i == $attempts means www.cbr.ru does not respond to the request 'http://www.cbr.ru/scripts/XML_daily_eng.asp?date_req=' . date("d/m/Y")
        if ($i == $attempts) {
            throw new Exception('Cannot get data from ' . $urlToXml);
        }

        // convert json into a php object and get the currency value from the object
        // making $attempts of attempts to get the json in case it is not received on the first request
        for ($i = 0; $i < $attempts; $i++){
            if ($jsonString = file_get_contents($urlToJson)){
                $this->rbcExchangeRate = json_decode($jsonString)->data->rate1;
                break;
            }
        }

        // $i == $attempts means cash.rbc.ru does not respond to the request 'https://cash.rbc.ru/cash/json/converter_currency_rate/?currency_from=' . $from . '&currency_to=' . $to . '&source=cbrf&sum=1&date='
        if ($i == $attempts) {
            throw new Exception('Cannot get data from ' . $urlToJson);
        }

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
        $exchangeRate->setRatio($this->getAverage());
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
<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use function Sodium\add;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\Entity\ExchangeRate;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ExRatesService
 * @package App\Service
 */
class ExRatesService
{
    private $from = null;
    private $to = null;
    private $sources = [];

    /**
     * ExRatesService constructor.
     * @param string $from
     * @param string $to
     */
    public function __construct(string $from, string $to)
    {
        if ($from === $to) {
            throw new Exception('There is no point in finding ratio of the same currency, it will always be 1!');
        }

        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @param BankSDK ...$sources
     */
    public function addSource(BankSDK ...$sources)
    {
        foreach ($sources as $source) {
            $this->sources[] = $source;
        }
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return float
     */
    public function getAverage() : float
    {
        $sum = 0;
        $sourcesLength = count($this->sources);

        for ($i = 0; $i < $sourcesLength; $i++) {
            $sum += $this->sources[$i]->fetch($this->from, $this->to);
        }

        return number_format($sum / $sourcesLength, 4);
    }

}
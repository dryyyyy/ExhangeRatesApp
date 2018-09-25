<?php
namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class RbcSDK
 * @package App\Service
 */
class RbcSDK implements BankSDK
{
    private $attempts;

    /**
     * RbcSDK constructor.
     * @param int $attempts
     */
    public function __construct(int $attempts)
    {
        $this->attempts = $attempts;
    }

    /**
     * @param string $from
     * @param string $to
     * @return float
     */
    public function fetch(string $from, string $to): float
    {
        $apiUrl = 'https://cash.rbc.ru/cash/json/converter_currency_rate/?currency_from=' .
            $from . '&currency_to=' . $to . '&source=cbrf&sum=1&date=';
        $exchangeRate = null;
        for ($i = 0; $i < $this->attempts; $i++) {
            if ($jsonString = file_get_contents($apiUrl)) {
                $exchangeRate = json_decode($jsonString)->data->rate1;
                break;
            }
        }

        if ($this->attempts == $i) {
            throw new Exception('Cannot fetch data from' . $apiUrl);
        }
        return $exchangeRate;
    }
}
<?php
namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;


/**
 * Class RbcSDK
 * @package App\Service
 */
class RbcSDK implements BankSDK{

    /**
     * @param string $from
     * @param string $to
     * @param int $attempts
     * @return float
     * @throws Exception
     */
    public function fetch(string $from, string $to, int $attempts): float
    {
        $apiUrl = 'https://cash.rbc.ru/cash/json/converter_currency_rate/?currency_from=' . $from . '&currency_to=' . $to . '&source=cbrf&sum=1&date=';
        $exchangeRate = null;
        for ($i = 0; $i < $attempts; $i++){
            if ($jsonString = file_get_contents($apiUrl)){
                $exchangeRate = json_decode($jsonString)->data->rate1;
                break;
            }
        }

        if($attempts == $i){
            throw new Exception('Cannot fetch data from' . $apiUrl);
        }

        return $exchangeRate;
    }
}
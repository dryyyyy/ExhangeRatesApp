<?php
namespace App\Service;

/**
 * Class RbcSDK
 * @package App\Service
 */
class RbcSDK implements BankSDK{

    private $apiUrl;
    private $from;
    private $to;

    /**
     * RbcSDK constructor.
     * @param string $from
     * @param string $to
     */
    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
        $this->apiUrl = 'https://cash.rbc.ru/cash/json/converter_currency_rate/?currency_from=' . $this->from . '&currency_to=' . $this->to . '&source=cbrf&sum=1&date=';;
    }

    /**
     * @param string $from
     * @param string $to
     * @param int $attempts
     * @return float
     * @throws Exception
     */
    public function fetch(string $from, string $to, int $attempts): float
    {
        $exchangeRate = null;
        for ($i = 0; $i < $attempts; $i++){
            if ($jsonString = file_get_contents($this->apiUrl)){
                $exchangeRate = json_decode($jsonString)->data->rate1;
                break;
            }
        }

        if($attempts == $i){
            throw new Exception('Cannot fetch data from' . $this->apiUrl);
        }

        return $exchangeRate;
    }
}
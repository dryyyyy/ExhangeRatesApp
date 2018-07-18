<?php
namespace App\Service;

/**
 * Class CbrSDK
 * @package App\Service
 */
class CbrSDK implements BankSDK{

    private $apiUrl;
    private $from;
    private $to;

    /**
     * CbrSDK constructor.
     * @param string $from
     * @param string $to
     */
    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
        $this->apiUrl = 'http://www.cbr.ru/scripts/XML_daily_eng.asp?date_req=' . date("d/m/Y");
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
            if ($tmpXml = file_get_contents($this->apiUrl)){
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
                $exchangeRate = number_format($xmlFrom / $xmlTo, 4);
                $exchangeRate = floatval($exchangeRate);
                break;
            }
        }

        if($attempts == $i){
            throw new Exception('Cannot fetch data from' . $this->apiUrl);
        }

        return $exchangeRate;
    }
}
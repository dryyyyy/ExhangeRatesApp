<?php
namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class CbrSDK
 * @package App\Service
 */
class CbrSDK implements BankSDK
{
    private $attempts;

    /**
     * CbrSDK constructor.
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
        $apiUrl = 'http://www.cbr.ru/scripts/XML_daily_eng.asp?date_req=' . date("d/m/Y");
        $exchangeRate = null;
        for ($i = 0; $i < $this->attempts; $i++) {
            if ($tmpXml = file_get_contents($apiUrl)) {
                $tmpXml = simplexml_load_string($tmpXml);

                // http://www.cbr.ru compares all currencies to RUR by default and does not contain RUR currency in the provided xml.
                //That's why if $this->from or $this->to is RUR they are set to 1
                if ($from != 'RUR') {
                    $xmlFrom = $tmpXml->xpath('/ValCurs/Valute/CharCode[.="' . $from . '"]/following-sibling::Value');
                    $xmlFrom = $xmlFrom[0][0];
                } else {
                    $xmlFrom = 1;
                }
                if ($to != 'RUR') {
                    $xmlTo = $tmpXml->xpath('/ValCurs/Valute/CharCode[.="' . $to . '"]/following-sibling::Value');
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

        if ($this->attempts == $i) {
            throw new Exception('Cannot fetch data from' . $apiUrl);
        }
        return $exchangeRate;
    }
}
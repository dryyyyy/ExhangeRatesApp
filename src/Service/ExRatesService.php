<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\Entity\ExchangeRate;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ExRatesService {

    private $from;
    private $to;
    private $cbrExchangeRate;
    private $rbcExchangeRate;

    public function __construct($from = 'USD', $to = 'RUR', $attempts = 10)
    {
        if ($from === $to) {
            throw new Exception('There is no point in finding ratio of the same currency, it will always be 1!');
        }

        $this->from = $from;
        $this->to = $to;
        $xmlFrom = null;
        $xmlTo = null;

        // request urls
        $urlToJson = 'https://cash.rbc.ru/cash/json/converter_currency_rate/?currency_from=' . $from . '&currency_to=' . $to . '&source=cbrf&sum=1&date=';
        $urlToXml = 'http://www.cbr.ru/scripts/XML_daily_eng.asp?date_req=' . date("d/m/Y");

        // preparing xml for xpath
        // making $attempts of attempts to get the xml in case it is not received on the first request
        for ($i = 0; $i < $attempts; $i++){
            if ($tmpXml = file_get_contents($urlToXml)){
                $tmpXml = simplexml_load_string($tmpXml);

                // http://www.cbr.ru compares all currencies to RUR by default and does not contain RUR currency in the provided xml. That's why if $from or $to is RUR they are set to 1
                if($from != 'RUR'){
                    $xmlFrom = $tmpXml->xpath('/ValCurs/Valute/CharCode[.="' . $from . '"]/following-sibling::Value');
                    $xmlFrom = $xmlFrom[0][0];
                } else {
                    $xmlFrom = 1;
                }
                if($to != 'RUR'){
                    $xmlTo = $tmpXml->xpath('/ValCurs/Valute/CharCode[.="' . $to . '"]/following-sibling::Value');
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
    public function putToDB(EntityManagerInterface $entityManager){
        $item = new ExchangeRate();
        $item->setFromCurrency($this->from);
        $item->setToCurrency($this->to);
        $item->setRatio($this->getAverage());
        $item->setDate(date("d-m-Y"));

        $entityManager->persist($item);
        $entityManager->flush();
    }

    public function fetch(EntityManagerInterface $entityManager, $date){
        $repository = $entityManager->getRepository(ExchangeRate::class);
        $item = $repository->findOneBy(['date' => $date]);

        $encoder = array(new JsonEncoder());
        $normalizer = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizer, $encoder);

        $jsonContent = $serializer->serialize($item, 'json');
        return $jsonContent;
    }
}
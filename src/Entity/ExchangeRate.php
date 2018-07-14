<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExchangeRateRepository")
 */
class ExchangeRate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", length=3)
     */
    private $fromCurrency;

    /**
     * @ORM\Column(type="text", length=3)
     */
    private $toCurrency;

    /**
     * @ORM\Column(type="float")
     */
    private $ratio;

    /**
     * @ORM\Column(type="string")
     */
    private $date;

    // Getters & Setters

    /**
     * @return integer
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFromCurrency(){
        return $this->fromCurrency;
    }

    /**
     * @param $currency
     * @return $this
     */
    public function setFromCurrency($currency){
        $this->fromCurrency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getToCurrency(){
        return $this->toCurrency;
    }

    /**
     * @param $currecy
     * @return $this
     */
    public function setToCurrency($currency){
        $this->toCurrency = $currency;

        return $this;
    }

    /**
     * @return float
     */
    public function getRatio(){
        return$this->ratio;
    }

    /**
     * @param $ratio
     * @return $this
     */
    public function setRatio($ratio){
        $this->ratio = $ratio;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate(){
        return $this->date;
    }

    /**
     * @param $date
     * @return $this
     */
    public function setDate($date){
        $this->date = $date;

        return $this;
    }
}
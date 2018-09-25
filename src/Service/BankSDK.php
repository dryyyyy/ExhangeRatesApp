<?php
namespace App\Service;

/**
 * Interface BankSDK
 * @package App\Service
 */
Interface BankSDK {

    public function __construct(int $attempts);

    /**
     * @param string $from
     * @param string $to
     * @param int $attempts
     * @return float
     */
    public function fetch(string $from, string $to):float;
}
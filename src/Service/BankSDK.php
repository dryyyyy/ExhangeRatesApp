<?php
namespace App\Service;

/**
 * Interface BankSDK
 * @package App\Service
 */
interface BankSDK
{
    /**
     * BankSDK constructor.
     * @param int $attempts
     */
    public function __construct(int $attempts);

    /**
     * @param string $from
     * @param string $to
     * @return float
     */
    public function fetch(string $from, string $to):float;
}
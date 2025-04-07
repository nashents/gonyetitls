<?php

namespace App\Services;

class UtilityService
{
    public function formatCurrency($amount, $currency = 'USD')
    {
        return number_format($amount, 2) . " " . $currency;
    }

    public function generateReferenceCode($prefix = 'REF')
    {
        return $prefix . '-' . strtoupper(uniqid());
    }
}
<?php

if (! function_exists('currency_idr')) {
    function currency_idr(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}



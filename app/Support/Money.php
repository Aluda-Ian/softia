<?php

namespace App\Support;

class Money
{
    public static function format(float|int|string|null $amount): string
    {
        if ($amount === null) {
            return 'On quotation';
        }

        return config('shop.currency').' '.number_format((float) $amount, 0);
    }
}

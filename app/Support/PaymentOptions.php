<?php

namespace App\Support;

use App\Enums\PaymentMethod;

/**
 * Retail payment methods enabled in Admin > System > Settings.
 */
class PaymentOptions
{
    /**
     * @return list<PaymentMethod>
     */
    public static function retail(): array
    {
        return array_values(array_filter([
            config('shop.payments.pay_on_delivery') ? PaymentMethod::PayOnDelivery : null,
            config('shop.payments.mpesa_manual') ? PaymentMethod::Mpesa : null,
        ]));
    }

    /**
     * With no retail method switched on, every checkout becomes a quote request.
     */
    public static function retailCheckoutAvailable(): bool
    {
        return self::retail() !== [];
    }
}

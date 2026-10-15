<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Invoice = 'invoice';
    case PayOnDelivery = 'pay_on_delivery';
    case Mpesa = 'mpesa';

    public function label(): string
    {
        return match ($this) {
            self::Invoice => 'Invoice after quotation',
            self::PayOnDelivery => 'Pay on delivery',
            self::Mpesa => 'M-Pesa',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}

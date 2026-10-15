<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderType: string implements HasColor, HasLabel
{
    case Quote = 'quote';
    case Order = 'order';

    public function label(): string
    {
        return match ($this) {
            self::Quote => 'Quotation request',
            self::Order => 'Retail order',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Quote => 'info',
            self::Order => 'primary',
        };
    }
}

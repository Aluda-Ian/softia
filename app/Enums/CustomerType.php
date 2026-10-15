<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CustomerType: string implements HasLabel
{
    case Business = 'b2b';
    case Retail = 'b2c';

    public function label(): string
    {
        return match ($this) {
            self::Business => 'Business / Institution',
            self::Retail => 'Individual',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}

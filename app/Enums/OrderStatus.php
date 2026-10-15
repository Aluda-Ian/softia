<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Quoted = 'quoted';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Dispatched = 'dispatched';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Quoted, self::Confirmed => 'info',
            self::Processing, self::Dispatched => 'primary',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }
}

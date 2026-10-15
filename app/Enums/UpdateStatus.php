<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UpdateStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Applied = 'applied';
    case Failed = 'failed';
    case RolledBack = 'rolled_back';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Ready to install',
            self::Applied => 'Installed',
            self::Failed => 'Failed',
            self::RolledBack => 'Rolled back',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'info',
            self::Applied => 'success',
            self::Failed => 'danger',
            self::RolledBack => 'gray',
        };
    }
}

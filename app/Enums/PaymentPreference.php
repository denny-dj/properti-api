<?php

namespace App\Enums;

enum PaymentPreference: string
{
    case Cash = 'cash';
    case Kpr = 'kpr';
    case Milestone = 'milestone';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Kpr => 'KPR',
            self::Milestone => 'Bertahap (milestone)',
        };
    }
}

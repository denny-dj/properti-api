<?php

namespace App\Enums;

enum ArchitecturalStyle: string
{
    case Minimalist = 'minimalist';
    case Modern = 'modern';
    case Japandi = 'japandi';
    case Industrial = 'industrial';

    public function label(): string
    {
        return match ($this) {
            self::Minimalist => 'Minimalis',
            self::Modern => 'Modern',
            self::Japandi => 'Japandi',
            self::Industrial => 'Industrial',
        };
    }
}

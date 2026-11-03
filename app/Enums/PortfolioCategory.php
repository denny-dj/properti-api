<?php

namespace App\Enums;

enum PortfolioCategory: string
{
    case Interior = 'interior';
    case KitchenSet = 'kitchen_set';
    case FullHouse = 'full_house';
    case Facade = 'facade';

    public function label(): string
    {
        return match ($this) {
            self::Interior => 'Interior',
            self::KitchenSet => 'Kitchen Set',
            self::FullHouse => 'Rumah Lengkap',
            self::Facade => 'Fasad',
        };
    }
}

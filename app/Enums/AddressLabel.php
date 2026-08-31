<?php

namespace App\Enums;

enum AddressLabel: string
{
    case Home = 'casa';
    case Office = 'oficina';
    case Other = 'otra';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Casa',
            self::Office => 'Oficina',
            self::Other => 'Otra',
        };
    }
}

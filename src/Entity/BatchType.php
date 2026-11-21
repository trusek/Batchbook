<?php

namespace App\Entity;

/**
 * Typ nastawu. Nowy typ = nowy przypadek z etykietą; nic więcej nie trzeba zmieniać.
 */
enum BatchType: string
{
    case Wine = 'wine';
    case Beer = 'beer';

    public function label(): string
    {
        return match ($this) {
            self::Wine => 'Wino',
            self::Beer => 'Piwo',
        };
    }
}

<?php

namespace App\Time;

use Psr\Clock\ClockInterface;

/**
 * Daty względne po polsku („wczoraj”, „za 3 dni”, „dzień 25”), liczone dniami kalendarzowymi
 * od dzisiejszej daty z zegara aplikacji. Godzina nie ma znaczenia: wpis z 23:55 jest „wczoraj”
 * już pięć minut po północy.
 */
final class RelativeDays
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }

    /**
     * Liczba dni kalendarzowych od dziś do dnia $date: dodatnia w przyszłości, ujemna w przeszłości.
     */
    public function daysFromToday(\DateTimeInterface $date): int
    {
        // Same daty kalendarzowe w UTC, żeby zmiana czasu nie zmieniała długości doby.
        $utc = new \DateTimeZone('UTC');
        $today = new \DateTimeImmutable($this->clock->now()->format('Y-m-d'), $utc);
        $day = new \DateTimeImmutable($date->format('Y-m-d'), $utc);

        return (int) $today->diff($day)->format('%r%a');
    }

    /**
     * „dziś”, „wczoraj”, „jutro”, „3 dni temu”, „za 3 dni”.
     */
    public function describe(\DateTimeInterface $date): string
    {
        $days = $this->daysFromToday($date);

        return match (true) {
            0 === $days => 'dziś',
            -1 === $days => 'wczoraj',
            1 === $days => 'jutro',
            $days < 0 => -$days.' dni temu',
            default => 'za '.$days.' dni',
        };
    }

    /**
     * Dzień nastawu od daty rozpoczęcia, w której dzień startu to „dzień 1”;
     * przed startem „start jutro” albo „start za 3 dni”.
     */
    public function batchDay(\DateTimeInterface $startDate): string
    {
        $days = $this->daysFromToday($startDate);

        return $days <= 0 ? 'dzień '.(1 - $days) : 'start '.$this->describe($startDate);
    }
}

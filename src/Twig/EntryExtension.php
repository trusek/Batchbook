<?php

namespace App\Twig;

use App\Entity\Entry;
use Psr\Clock\ClockInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

/**
 * Test Twig `entry is future`: wpis przyszły, czyli czas wpisu późniejszy niż teraz (zegar aplikacji).
 */
final class EntryExtension extends AbstractExtension
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function getTests(): array
    {
        return [
            new TwigTest('future', $this->isFuture(...)),
        ];
    }

    public function isFuture(Entry $entry): bool
    {
        return $entry->getOccurredAt() > $this->clock->now();
    }
}

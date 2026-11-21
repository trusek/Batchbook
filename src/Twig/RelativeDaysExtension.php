<?php

namespace App\Twig;

use App\Time\RelativeDays;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Filtry dat względnych (App\Time\RelativeDays):
 * `date|relative_day` („wczoraj”, „za 3 dni”) i `startDate|batch_day` („dzień 25”, „start za 3 dni”).
 */
final class RelativeDaysExtension extends AbstractExtension
{
    public function __construct(private readonly RelativeDays $relativeDays)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('relative_day', $this->relativeDays->describe(...)),
            new TwigFilter('batch_day', $this->relativeDays->batchDay(...)),
        ];
    }
}

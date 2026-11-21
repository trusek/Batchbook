<?php

namespace App\Sidebar;

use App\Entity\Batch;
use App\Repository\BatchRepository;
use App\Repository\EntryRepository;
use Psr\Clock\ClockInterface;

/**
 * Dane paska bocznego (lista nastawów na każdej stronie): nieusunięte nastawy w kolejności listy
 * nastawów, każdy z datą ostatniego wpisu i najbliższym planem. Trzy zapytania niezależnie od liczby
 * nastawów (daty liczone zbiorczo, bez N+1).
 */
final class Sidebar
{
    public function __construct(
        private readonly BatchRepository $batches,
        private readonly EntryRepository $entries,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return list<SidebarBatch>
     */
    public function batches(): array
    {
        $now = $this->clock->now();
        $lastEntryDates = $this->entries->findLastEntryDates($now);
        $nextPlanDates = $this->entries->findNextPlanDates($now);

        return array_map(
            static fn (Batch $batch): SidebarBatch => new SidebarBatch(
                $batch,
                $lastEntryDates[$batch->getId()] ?? null,
                $nextPlanDates[$batch->getId()] ?? null,
            ),
            $this->batches->findForList(),
        );
    }
}

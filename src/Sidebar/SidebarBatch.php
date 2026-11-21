<?php

namespace App\Sidebar;

use App\Entity\Batch;

/**
 * Pozycja paska bocznego: nastaw z datą ostatniego wpisu (bez wpisów przyszłych) i czasem najbliższego planu.
 */
final readonly class SidebarBatch
{
    public function __construct(
        public Batch $batch,
        public ?\DateTimeImmutable $lastEntryAt,
        public ?\DateTimeImmutable $nextPlanAt,
    ) {
    }
}

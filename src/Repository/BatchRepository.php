<?php

namespace App\Repository;

use App\Entity\Batch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Batch>
 */
class BatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Batch::class);
    }

    /**
     * Zapisuje nastaw: nowy (dodanie) lub zmieniony (edycja, miękkie usunięcie).
     */
    public function save(Batch $batch): void
    {
        $this->getEntityManager()->persist($batch);
        $this->getEntityManager()->flush();
    }

    public function findVisible(int $id): ?Batch
    {
        return $this->findOneBy(['id' => $id, 'deletedAt' => null]);
    }

    /**
     * Nieusunięte nastawy od najnowszych: po dacie rozpoczęcia (liczonej jako początek dnia),
     * a gdy jej brak, po dacie utworzenia. Przy równej dacie sortowania wyżej nastaw utworzony później.
     *
     * Sortowanie w PHP na obiektach dat, bo w bazie data rozpoczęcia (DATE) i data utworzenia
     * (DATETIME) mają różne formaty i nie porównują się wprost.
     *
     * @return list<Batch>
     */
    public function findForList(): array
    {
        $batches = $this->findBy(['deletedAt' => null]);

        usort($batches, static fn (Batch $a, Batch $b): int => [self::sortDate($b), $b->getCreatedAt(), $b->getId()]
            <=> [self::sortDate($a), $a->getCreatedAt(), $a->getId()]);

        return $batches;
    }

    private static function sortDate(Batch $batch): \DateTimeImmutable
    {
        return $batch->getStartDate()?->setTime(0, 0) ?? $batch->getCreatedAt();
    }
}

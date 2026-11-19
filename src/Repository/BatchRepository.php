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

    public function add(Batch $batch): void
    {
        $this->getEntityManager()->persist($batch);
        $this->getEntityManager()->flush();
    }

    /**
     * Zapisuje zmiany w nastawie już zarządzanym przez Doctrine (edycja, miękkie usunięcie).
     */
    public function save(Batch $batch): void
    {
        $this->getEntityManager()->flush();
    }

    public function findVisible(int $id): ?Batch
    {
        return $this->findOneBy(['id' => $id, 'deletedAt' => null]);
    }

    /**
     * Nieusunięte nastawy od najnowszych: po dacie rozpoczęcia, a gdy jej brak, po dacie utworzenia.
     *
     * @return list<Batch>
     */
    public function findForList(): array
    {
        return $this->createQueryBuilder('b')
            ->addSelect('COALESCE(b.startDate, b.createdAt) AS HIDDEN sortDate')
            ->where('b.deletedAt IS NULL')
            ->orderBy('sortDate', 'DESC')
            ->addOrderBy('b.createdAt', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

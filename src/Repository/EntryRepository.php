<?php

namespace App\Repository;

use App\Entity\Batch;
use App\Entity\Entry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entry>
 */
class EntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entry::class);
    }

    public function add(Entry $entry): void
    {
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
    }

    /**
     * Dziennik nastawu: nieusunięte wpisy od najpóźniejszego czasu wpisu;
     * przy równym czasie wyżej wpis dodany później.
     *
     * @return list<Entry>
     */
    public function findJournal(Batch $batch): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.batch = :batch')
            ->andWhere('e.deletedAt IS NULL')
            ->setParameter('batch', $batch)
            ->orderBy('e.occurredAt', 'DESC')
            ->addOrderBy('e.createdAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

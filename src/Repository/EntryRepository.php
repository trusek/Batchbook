<?php

namespace App\Repository;

use App\Entity\Batch;
use App\Entity\Entry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
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
     * Zapisuje zmiany zarządzanego wpisu (edycja, miękkie usunięcie).
     */
    public function save(Entry $entry): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * Wpis widoczny w aplikacji: nieusunięty i należący do nieusuniętego nastawu.
     */
    public function findVisible(int $id): ?Entry
    {
        return $this->createQueryBuilder('e')
            ->join('e.batch', 'b')
            ->where('e.id = :id')
            ->andWhere('e.deletedAt IS NULL')
            ->andWhere('b.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Data ostatniego wpisu każdego nastawu: najpóźniejszy czas nieusuniętego wpisu,
     * który nie jest późniejszy niż $now (wpisy przyszłe się nie liczą).
     * Nastawy bez takiego wpisu nie występują w wyniku.
     *
     * @return array<int, \DateTimeImmutable> id nastawu => data ostatniego wpisu
     */
    public function findLastEntryDates(\DateTimeImmutable $now): array
    {
        $rows = $this->createQueryBuilder('e')
            ->select('IDENTITY(e.batch) AS batchId', 'MAX(e.occurredAt) AS lastEntryAt')
            ->where('e.deletedAt IS NULL')
            ->andWhere('e.occurredAt <= :now')
            ->setParameter('now', $now, Types::DATETIME_IMMUTABLE)
            ->groupBy('e.batch')
            ->getQuery()
            ->getArrayResult();

        $dates = [];
        foreach ($rows as $row) {
            $dates[(int) $row['batchId']] = new \DateTimeImmutable($row['lastEntryAt']);
        }

        return $dates;
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

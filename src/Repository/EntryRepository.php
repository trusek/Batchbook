<?php

namespace App\Repository;

use App\Entity\Batch;
use App\Entity\Entry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Zapisuje wpis: nowy (szybki wpis) lub zmieniony (edycja, miękkie usunięcie).
     */
    public function save(Entry $entry): void
    {
        $this->getEntityManager()->persist($entry);
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
     * Data ostatniego wpisu każdego nieusuniętego nastawu: najpóźniejszy czas nieusuniętego wpisu,
     * który nie jest późniejszy niż $now (wpisy przyszłe się nie liczą).
     * Nastawy bez takiego wpisu nie występują w wyniku.
     *
     * @return array<int, \DateTimeImmutable> id nastawu => data ostatniego wpisu
     */
    public function findLastEntryDates(\DateTimeImmutable $now): array
    {
        return $this->findDatePerBatch('MAX', 'e.occurredAt <= :now', $now);
    }

    /**
     * Najbliższy plan każdego nieusuniętego nastawu: najwcześniejszy czas nieusuniętego wpisu przyszłego
     * (późniejszego niż $now). Nastawy bez planów nie występują w wyniku.
     *
     * @return array<int, \DateTimeImmutable> id nastawu => czas najbliższego planu
     */
    public function findNextPlanDates(\DateTimeImmutable $now): array
    {
        return $this->findDatePerBatch('MIN', 'e.occurredAt > :now', $now);
    }

    /**
     * Najbliższe plany ze wszystkich nieusuniętych nastawów: wpisy przyszłe od najwcześniejszego.
     *
     * @return list<Entry> wpisy z załadowanym nastawem
     */
    public function findUpcoming(\DateTimeImmutable $now, int $limit): array
    {
        return $this->visibleWithBatch()
            ->andWhere('e.occurredAt > :now')
            ->setParameter('now', $now, Types::DATETIME_IMMUTABLE)
            ->orderBy('e.occurredAt', 'ASC')
            ->addOrderBy('e.createdAt', 'ASC')
            ->addOrderBy('e.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Ostatnia aktywność ze wszystkich nieusuniętych nastawów: wpisy nie późniejsze niż $now,
     * od najpóźniejszego (przy równym czasie wyżej dodany później, jak w dzienniku).
     *
     * @return list<Entry> wpisy z załadowanym nastawem
     */
    public function findRecent(\DateTimeImmutable $now, int $limit): array
    {
        return $this->visibleWithBatch()
            ->andWhere('e.occurredAt <= :now')
            ->setParameter('now', $now, Types::DATETIME_IMMUTABLE)
            ->orderBy('e.occurredAt', 'DESC')
            ->addOrderBy('e.createdAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Nieusunięte wpisy nieusuniętych nastawów, z nastawem ładowanym w tym samym zapytaniu.
     */
    private function visibleWithBatch(): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->addSelect('b')
            ->join('e.batch', 'b')
            ->where('e.deletedAt IS NULL')
            ->andWhere('b.deletedAt IS NULL');
    }

    /**
     * Jedno zapytanie zbiorcze: $aggregate (MIN/MAX) czasu widocznych wpisów spełniających $condition, per nastaw.
     *
     * @param 'MIN'|'MAX' $aggregate
     *
     * @return array<int, \DateTimeImmutable> id nastawu => data
     */
    private function findDatePerBatch(string $aggregate, string $condition, \DateTimeImmutable $now): array
    {
        $rows = $this->createQueryBuilder('e')
            ->select('IDENTITY(e.batch) AS batchId', $aggregate.'(e.occurredAt) AS entryDate')
            ->join('e.batch', 'b')
            ->where('e.deletedAt IS NULL')
            ->andWhere('b.deletedAt IS NULL')
            ->andWhere($condition)
            ->setParameter('now', $now, Types::DATETIME_IMMUTABLE)
            ->groupBy('e.batch')
            ->getQuery()
            ->getArrayResult();

        $dates = [];
        foreach ($rows as $row) {
            $dates[(int) $row['batchId']] = new \DateTimeImmutable($row['entryDate']);
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

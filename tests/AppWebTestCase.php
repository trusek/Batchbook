<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Baza testów funkcjonalnych: wszystko przez HTTP, zegar zamrożony.
 *
 * - Każdy test dostaje świeżego klienta ($this->client).
 * - Zegar (ClockInterface) jest zamrożony na DEFAULT_NOW; zmień go przez
 *   $this->setNow('2026-03-18 09:30'). Czas jest w strefie Europe/Warsaw.
 * - Baza testowa jest tworzona z migracji raz na przebieg (tests/bootstrap.php),
 *   a DAMA DoctrineTestBundle cofa zmiany po każdym teście, więc każdy test
 *   startuje na pustej bazie.
 * - Wspólne kroki: addBatch(), openBatch(), addEntry(); odczyt widoków:
 *   batchRows(), journal().
 */
abstract class AppWebTestCase extends WebTestCase
{
    use ClockSensitiveTrait;

    protected const DEFAULT_NOW = '2026-03-15 12:00:00';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->setNow(self::DEFAULT_NOW);
    }

    /**
     * Ustawia bieżący czas widziany przez aplikację (lokalny czas Europe/Warsaw).
     */
    protected function setNow(string $localDateTime): void
    {
        static::mockTime(new \DateTimeImmutable($localDateTime, new \DateTimeZone('Europe/Warsaw')));
    }

    /**
     * Wysyła formularz nowego nastawu. Nie podąża za przekierowaniem
     * (sukces: 303 na listę, błąd walidacji: 422 z formularzem).
     */
    protected function addBatch(string $name, string $type = 'wine', string $startDate = '', string $description = ''): void
    {
        $this->client->request('GET', '/nastawy/nowy');
        $this->client->submitForm('Zapisz', [
            'batch[name]' => $name,
            'batch[type]' => $type,
            'batch[startDate]' => $startDate,
            'batch[description]' => $description,
        ]);
    }

    /**
     * Otwiera stronę nastawu, klikając jego nazwę na liście nastawów.
     */
    protected function openBatch(string $name): Crawler
    {
        $crawler = $this->client->request('GET', '/');

        return $this->client->click($crawler->filter('[data-batch]')->selectLink($name)->link());
    }

    /**
     * Wysyła formularz szybkiego wpisu na aktualnie otwartej stronie nastawu.
     * Po sukcesie podąża za przekierowaniem i zwraca stronę nastawu;
     * po błędzie walidacji zwraca stronę z formularzem (422).
     *
     * @param string $occurredAt format pola datetime-local, np. '2026-03-15T12:00'; null = zostaw domyślny
     */
    protected function addEntry(string $note, ?string $occurredAt = null): Crawler
    {
        $values = ['entry[note]' => $note];
        if (null !== $occurredAt) {
            $values['entry[occurredAt]'] = $occurredAt;
        }

        $crawler = $this->client->submitForm('Dodaj wpis', $values);

        return $this->client->getResponse()->isRedirect() ? $this->client->followRedirect() : $crawler;
    }

    /**
     * @return list<array{string, string, string}> nazwa, typ, data rozpoczęcia
     */
    protected function batchRows(Crawler $crawler): array
    {
        return $crawler->filter('[data-batch]')->each(static fn (Crawler $row): array => [
            trim($row->filter('[data-batch-name]')->text()),
            trim($row->filter('[data-batch-type]')->text()),
            trim($row->filter('[data-batch-start-date]')->text()),
        ]);
    }

    /**
     * Dziennik na stronie nastawu, w kolejności wyświetlania.
     *
     * @return list<array{string, string}> czas wpisu (dd.mm.rrrr HH:MM), notatka
     */
    protected function journal(Crawler $crawler): array
    {
        return $crawler->filter('[data-entry]')->each(static fn (Crawler $entry): array => [
            trim($entry->filter('[data-entry-occurred-at]')->text()),
            trim($entry->filter('[data-entry-note]')->text()),
        ]);
    }
}

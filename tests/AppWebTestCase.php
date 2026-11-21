<?php

namespace App\Tests;

use App\Kernel;
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
 * - Lista nastawów jest w pasku bocznym na każdej stronie ([data-batch] w nawigacji „Nastawy”).
 * - Wspólne akcje: addBatch(), openBatch(), openBatchEdit(), deleteBatch(), addEntry(), openEntryEdit(),
 *   editEntry(), deleteEntry(); odczyt widoków: batchRows(), lastEntryDates(), sidebar(), journal(),
 *   entryWithNote().
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
        static::mockTime(new \DateTimeImmutable($localDateTime, new \DateTimeZone(Kernel::TIMEZONE)));
    }

    /**
     * Wysyła formularz nowego nastawu. Nie podąża za przekierowaniem
     * (sukces: 303 na pulpit, błąd walidacji: 422 z formularzem).
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
     * Otwiera stronę nastawu, klikając jego nazwę w pasku bocznym pulpitu.
     */
    protected function openBatch(string $name): Crawler
    {
        $crawler = $this->client->request('GET', '/');

        return $this->client->click($this->sidebarItem($crawler, $name)->filter('a')->link());
    }

    /**
     * Otwiera stronę edycji nastawu (ołówek „Edytuj nastaw” w nagłówku strony nastawu).
     */
    protected function openBatchEdit(string $name): Crawler
    {
        $this->openBatch($name);

        return $this->client->clickLink('Edytuj nastaw');
    }

    /**
     * Usuwa nastaw przyciskiem „Usuń nastaw” na stronie jego edycji. Nie podąża za przekierowaniem.
     *
     * @param array<string, string> $values np. ['_csrf_token' => 'zly'] do testów odrzucenia
     */
    protected function deleteBatch(string $name, array $values = []): void
    {
        $this->openBatchEdit($name);
        $this->client->submitForm('Usuń nastaw', $values);
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

        $crawler = $this->client->submitForm('Dodaj', $values);

        return $this->client->getResponse()->isRedirect() ? $this->client->followRedirect() : $crawler;
    }

    /**
     * Wpis w dzienniku (element [data-entry]) o podanej notatce, na stronie nastawu.
     */
    protected function entryWithNote(Crawler $page, string $note): Crawler
    {
        $entry = $page->filter('[data-entry]')->reduce(
            static fn (Crawler $e): bool => trim($e->filter('[data-entry-note]')->text()) === $note,
        );
        self::assertCount(1, $entry, sprintf('Brak wpisu „%s” w dzienniku.', $note));

        return $entry;
    }

    /**
     * Klika ołówek „Edytuj wpis …” przy wpisie o podanej notatce na aktualnie otwartej stronie nastawu.
     * Zwraca odpowiedź z formularzem edycji.
     */
    protected function openEntryEdit(string $note): Crawler
    {
        $entry = $this->entryWithNote($this->client->getCrawler(), $note);

        return $this->client->click($entry->selectLink('Edytuj wpis')->link());
    }

    /**
     * Edytuje wpis o podanej notatce (pola entry_edit[occurredAt|note]).
     * Po sukcesie podąża za przekierowaniem i zwraca stronę nastawu;
     * po błędzie walidacji zwraca odpowiedź z formularzem (422).
     *
     * @param array<string, string> $values
     */
    protected function editEntry(string $note, array $values): Crawler
    {
        $this->openEntryEdit($note);
        $crawler = $this->client->submitForm('Zapisz', $values);

        return $this->client->getResponse()->isRedirect() ? $this->client->followRedirect() : $crawler;
    }

    /**
     * Usuwa wpis o podanej notatce z aktualnie otwartej strony nastawu:
     * otwiera edycję wpisu i klika w niej „Usuń wpis”. Nie podąża za przekierowaniem.
     *
     * @param array<string, string> $values np. ['_csrf_token' => 'zly'] do testów odrzucenia
     */
    protected function deleteEntry(string $note, array $values = []): void
    {
        $this->openEntryEdit($note);
        $this->client->submitForm('Usuń wpis', $values);
    }

    /**
     * Pozycja nastawu o podanej nazwie w pasku bocznym.
     */
    protected function sidebarItem(Crawler $page, string $name): Crawler
    {
        $item = $page->filter('[data-batch]')->reduce(
            static fn (Crawler $b): bool => trim($b->filter('[data-batch-name]')->text()) === $name,
        );
        self::assertGreaterThan(0, $item->count(), sprintf('Brak nastawu „%s” w pasku bocznym.', $name));

        return $item->first();
    }

    /**
     * Daty ostatniego wpisu z paska bocznego (pełna data z atrybutu title).
     *
     * @return array<string, string> nazwa nastawu => data ostatniego wpisu (dd.mm.rrrr HH:MM lub pusta)
     */
    protected function lastEntryDates(Crawler $crawler): array
    {
        $dates = [];
        $crawler->filter('[data-batch]')->each(static function (Crawler $row) use (&$dates): void {
            $dates[trim($row->filter('[data-batch-name]')->text())] = (string) $row->filter('[data-batch-last-entry-at]')->attr('title');
        });

        return $dates;
    }

    /**
     * Nastawy w pasku bocznym, w kolejności wyświetlania.
     *
     * @return list<array{string, string, string}> nazwa, typ, data rozpoczęcia (dd.mm.rrrr z atrybutu title lub pusta)
     */
    protected function batchRows(Crawler $crawler): array
    {
        return $crawler->filter('[data-batch]')->each(static fn (Crawler $row): array => [
            trim($row->filter('[data-batch-name]')->text()),
            trim($row->filter('[data-batch-type]')->text()),
            (string) $row->filter('[data-batch-start-date]')->attr('title'),
        ]);
    }

    /**
     * Teksty paska bocznego, tak jak widzi je użytkownik (daty względne).
     *
     * @return array<string, array{type: string, start: string, last: string, plan: string}> nazwa nastawu => teksty
     */
    protected function sidebar(Crawler $crawler): array
    {
        $items = [];
        $crawler->filter('[data-batch]')->each(static function (Crawler $row) use (&$items): void {
            $plan = $row->filter('[data-batch-next-plan]');
            $items[trim($row->filter('[data-batch-name]')->text())] = [
                'type' => trim($row->filter('[data-batch-type]')->text()),
                'start' => trim($row->filter('[data-batch-start-date]')->text()),
                'last' => trim($row->filter('[data-batch-last-entry-at]')->text()),
                'plan' => $plan->count() > 0 ? trim($plan->text()) : '',
            ];
        });

        return $items;
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

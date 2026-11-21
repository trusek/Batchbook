<?php

namespace App\Tests;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Pulpit (ekran startowy): najbliższe plany i ostatnia aktywność ze wszystkich nastawów.
 */
final class DashboardTest extends AppWebTestCase
{
    public function testShowsUpcomingPlansAscendingAndRecentActivityDescendingAcrossBatches(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-03-01');
        $this->addBatch('Piwo pszeniczne', 'beer', '2026-03-12');
        $this->openBatch('Wino z aronii');
        $this->addEntry('nastawiono', '2026-03-01T18:00');
        $this->addEntry('sprawdzić Blg', '2026-03-18T12:00');
        $this->addEntry('dodać pożywkę', '2026-03-16T09:00');
        $this->openBatch('Piwo pszeniczne');
        $this->addEntry('zadano drożdże', '2026-03-15T12:00');
        $this->addEntry('zlać do butelek', '2026-03-20T10:00');
        $this->addEntry('zacieranie', '2026-03-12T10:00');

        $dashboard = $this->client->request('GET', '/');

        self::assertSame([
            ['16.03.2026 09:00', 'Wino z aronii', 'dodać pożywkę'],
            ['18.03.2026 12:00', 'Wino z aronii', 'sprawdzić Blg'],
            ['20.03.2026 10:00', 'Piwo pszeniczne', 'zlać do butelek'],
        ], $this->feed($dashboard, 'Najbliższe plany'));
        self::assertSame([
            ['15.03.2026 12:00', 'Piwo pszeniczne', 'zadano drożdże'],
            ['12.03.2026 10:00', 'Piwo pszeniczne', 'zacieranie'],
            ['01.03.2026 18:00', 'Wino z aronii', 'nastawiono'],
        ], $this->feed($dashboard, 'Ostatnia aktywność'));
    }

    public function testSkipsDeletedEntriesAndEntriesOfDeletedBatches(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-03-01');
        $this->addBatch('Pomyłka', 'beer', '2026-03-10');
        $this->openBatch('Wino z aronii');
        $this->addEntry('nastawiono', '2026-03-01T18:00');
        $this->addEntry('zły wpis', '2026-03-14T08:00');
        $this->addEntry('zły plan', '2026-03-17T08:00');
        $this->deleteEntry('zły wpis');
        $this->openBatch('Wino z aronii');
        $this->deleteEntry('zły plan');
        $this->openBatch('Pomyłka');
        $this->addEntry('wpis pomyłki', '2026-03-13T08:00');
        $this->addEntry('plan pomyłki', '2026-03-19T08:00');
        $this->deleteBatch('Pomyłka');

        $dashboard = $this->client->request('GET', '/');

        self::assertSame([], $this->feed($dashboard, 'Najbliższe plany'));
        self::assertSelectorTextContains('main', 'Nic nie zaplanowano.');
        self::assertSame([['01.03.2026 18:00', 'Wino z aronii', 'nastawiono']], $this->feed($dashboard, 'Ostatnia aktywność'));
    }

    public function testPlanBecomesActivityOnceItsTimePasses(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $this->addEntry('sprawdzić Blg', '2026-03-18T09:00');

        $this->setNow('2026-03-18 09:00');
        $dashboard = $this->client->request('GET', '/');

        self::assertSame([], $this->feed($dashboard, 'Najbliższe plany'));
        self::assertSame([['18.03.2026 09:00', 'Wino z aronii', 'sprawdzić Blg']], $this->feed($dashboard, 'Ostatnia aktywność'));
    }

    public function testBatchNameLeadsToBatchPage(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $this->addEntry('nastawiono');

        $dashboard = $this->client->request('GET', '/');
        $this->client->click($this->feedSection($dashboard, 'Ostatnia aktywność')->selectLink('Wino z aronii')->link());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Wino z aronii');
    }

    public function testEmptyDashboardInvitesToAddFirstBatch(): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertSelectorTextContains('main', 'Nie ma jeszcze żadnych nastawów');
        $this->client->click($crawler->filter('main')->selectLink('Dodaj nastaw')->link());
        self::assertSelectorTextContains('h1', 'Nowy nastaw');
    }

    private function feedSection(Crawler $dashboard, string $heading): Crawler
    {
        $section = $dashboard->filter('main section')->reduce(
            static fn (Crawler $s): bool => trim($s->filter('h2')->text()) === $heading,
        );
        self::assertCount(1, $section, sprintf('Brak sekcji „%s” na pulpicie.', $heading));

        return $section;
    }

    /**
     * @return list<array{string, string, string}> czas wpisu, nazwa nastawu, notatka
     */
    private function feed(Crawler $dashboard, string $heading): array
    {
        return $this->feedSection($dashboard, $heading)->filter('[data-feed-entry]')->each(static fn (Crawler $e): array => [
            trim($e->filter('[data-feed-occurred-at]')->text()),
            trim($e->filter('[data-feed-batch]')->text()),
            trim($e->filter('[data-feed-note]')->text()),
        ]);
    }
}

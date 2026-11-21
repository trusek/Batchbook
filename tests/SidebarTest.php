<?php

namespace App\Tests;

/**
 * Pasek boczny z nastawami: na każdej stronie, daty względne liczone dniami kalendarzowymi z zegara,
 * pełna data w atrybutach datetime i title.
 */
final class SidebarTest extends AppWebTestCase
{
    public function testShowsTypeAndRelativeDatesWithFullDatesInTimeElements(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-02-19');
        $this->addBatch('Piwo na święta', 'beer', '2026-03-18');
        $this->addBatch('Miód bez daty', 'wine', '');
        $this->openBatch('Wino z aronii');
        $this->addEntry('zlano znad osadu', '2026-03-14T20:00');
        $this->addEntry('sprawdzić Blg', '2026-03-19T08:00');
        $this->addEntry('zlać ponownie', '2026-03-25T08:00');
        $this->openBatch('Piwo na święta');
        $this->addEntry('kupić słód', '2026-03-12T10:00');

        $page = $this->client->request('GET', '/');

        self::assertSame([
            'Piwo na święta' => ['type' => 'Piwo', 'start' => 'start za 3 dni', 'last' => '3 dni temu', 'plan' => ''],
            'Miód bez daty' => ['type' => 'Wino', 'start' => 'bez daty startu', 'last' => 'brak wpisów', 'plan' => ''],
            'Wino z aronii' => ['type' => 'Wino', 'start' => 'dzień 25', 'last' => 'wczoraj', 'plan' => 'plan za 4 dni'],
        ], $this->sidebar($page));

        $wine = $this->sidebarItem($page, 'Wino z aronii');
        self::assertSame('2026-02-19', $wine->filter('time[data-batch-start-date]')->attr('datetime'));
        self::assertSame('19.02.2026', $wine->filter('time[data-batch-start-date]')->attr('title'));
        self::assertSame('2026-03-14T20:00', $wine->filter('time[data-batch-last-entry-at]')->attr('datetime'));
        self::assertSame('14.03.2026 20:00', $wine->filter('time[data-batch-last-entry-at]')->attr('title'));
        self::assertSame('2026-03-19T08:00', $wine->filter('time[data-batch-next-plan]')->attr('datetime'));
        self::assertSame('19.03.2026 08:00', $wine->filter('time[data-batch-next-plan]')->attr('title'));
    }

    public function testRelativeDatesCountCalendarDays(): void
    {
        $this->setNow('2026-03-15 00:05');
        $this->addBatch('Wino z aronii', 'wine', '2026-03-15');
        $this->addBatch('Piwo', 'beer', '2026-03-16');
        $this->openBatch('Wino z aronii');
        $this->addEntry('późny wieczór', '2026-03-14T23:55');
        $this->addEntry('rano', '2026-03-16T07:00');
        $this->openBatch('Piwo');
        $this->addEntry('przed chwilą', '2026-03-15T00:01');
        $this->addEntry('za chwilę', '2026-03-15T00:10');

        $sidebar = $this->sidebar($this->client->request('GET', '/'));

        self::assertSame(['type' => 'Wino', 'start' => 'dzień 1', 'last' => 'wczoraj', 'plan' => 'plan jutro'], $sidebar['Wino z aronii']);
        self::assertSame(['type' => 'Piwo', 'start' => 'start jutro', 'last' => 'dziś', 'plan' => 'plan dziś'], $sidebar['Piwo']);
    }

    public function testNextPlanSkipsDeletedEntries(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-03-01');
        $this->openBatch('Wino z aronii');
        $this->addEntry('odwołany plan', '2026-03-16T08:00');
        $this->addEntry('właściwy plan', '2026-03-20T08:00');
        $this->deleteEntry('odwołany plan');

        $sidebar = $this->sidebar($this->client->request('GET', '/'));

        self::assertSame('plan za 5 dni', $sidebar['Wino z aronii']['plan']);
    }

    public function testIsOnEveryPageAndMarksCurrentBatch(): void
    {
        $this->addBatch('Wino z aronii');
        $this->addBatch('Piwo pszeniczne', 'beer');

        foreach (['/', '/nastawy/nowy'] as $url) {
            $page = $this->client->request('GET', $url);
            self::assertSame(['Piwo pszeniczne', 'Wino z aronii'], array_column($this->batchRows($page), 0), $url);
        }

        $page = $this->openBatch('Wino z aronii');
        self::assertCount(2, $page->filter('[data-batch]'));
        self::assertSame('page', $this->sidebarItem($page, 'Wino z aronii')->filter('a')->attr('aria-current'));
        self::assertNull($this->sidebarItem($page, 'Piwo pszeniczne')->filter('a')->attr('aria-current'));

        $edit = $this->client->clickLink('Edytuj nastaw');
        self::assertCount(2, $edit->filter('[data-batch]'));
        self::assertSame('page', $this->sidebarItem($edit, 'Wino z aronii')->filter('a')->attr('aria-current'));
    }

    public function testSidebarOffersNewBatchLink(): void
    {
        $page = $this->client->request('GET', '/');

        $this->client->click($page->filter('aside')->selectLink('Nowy nastaw')->link());

        self::assertSelectorTextContains('h1', 'Nowy nastaw');
    }
}

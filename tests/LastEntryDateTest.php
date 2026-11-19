<?php

namespace App\Tests;

final class LastEntryDateTest extends AppWebTestCase
{
    public function testListShowsLatestEntryNotLaterThanNowSkippingFutureEntries(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-03-01');
        $this->openBatch('Wino z aronii');
        $this->addEntry('nastawione', '2026-03-01T10:00');
        $this->addEntry('zlane z osadu', '2026-03-14T19:45');
        $this->addEntry('drożdże dodane', '2026-03-05T08:00');
        $this->addEntry('sprawdzić Blg', '2026-03-18T12:00');

        $crawler = $this->client->request('GET', '/');

        self::assertSame(['Wino z aronii' => '14.03.2026 19:45'], $this->lastEntryDates($crawler));
    }

    public function testBatchWithoutEntriesOrWithOnlyFutureEntriesHasEmptyLastEntryDate(): void
    {
        $this->addBatch('Piwo pszeniczne', 'beer', '2026-03-10');
        $this->addBatch('Miód z lipy', 'wine', '2026-03-05');
        $this->openBatch('Miód z lipy');
        $this->addEntry('sprawdzić Blg', '2026-03-15T12:01');

        $crawler = $this->client->request('GET', '/');

        self::assertSame(['Piwo pszeniczne' => '', 'Miód z lipy' => ''], $this->lastEntryDates($crawler));
    }

    public function testFutureEntryCountsOnceItsTimePasses(): void
    {
        $this->addBatch('Miód z lipy', 'wine', '2026-03-05');
        $this->openBatch('Miód z lipy');
        $this->addEntry('nastawione', '2026-03-05T10:00');
        $this->addEntry('sprawdzić Blg', '2026-03-18T09:00');

        $this->setNow('2026-03-18 09:00');
        $crawler = $this->client->request('GET', '/');

        self::assertSame(['Miód z lipy' => '18.03.2026 09:00'], $this->lastEntryDates($crawler));
    }
}

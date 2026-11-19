<?php

namespace App\Tests;

use Symfony\Component\DomCrawler\Crawler;

final class FutureEntryTest extends AppWebTestCase
{
    public function testEntryInThreeDaysIsMarkedAsFuture(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');

        $crawler = $this->addEntry('sprawdzić Blg', '2026-03-18T12:00');

        self::assertSame(['sprawdzić Blg' => true], $this->futureFlags($crawler));
    }

    public function testEntryLaterTodayIsFutureUntilItsTimePasses(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $this->addEntry('zlać z osadu', '2026-03-15T18:30');
        $crawler = $this->addEntry('drożdże dodane', '2026-03-15T08:00');

        self::assertSame(['zlać z osadu' => true, 'drożdże dodane' => false], $this->futureFlags($crawler));

        $this->setNow('2026-03-15 18:31');
        $crawler = $this->openBatch('Wino z aronii');

        self::assertSame(['zlać z osadu' => false, 'drożdże dodane' => false], $this->futureFlags($crawler));
    }

    public function testEntryAtExactlyNowIsNotFuture(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');

        $crawler = $this->addEntry('nastawione', '2026-03-15T12:00');

        self::assertSame(['nastawione' => false], $this->futureFlags($crawler));
    }

    /**
     * @return array<string, bool> notatka => czy wpis ma znacznik wpisu przyszłego
     */
    private function futureFlags(Crawler $crawler): array
    {
        $flags = [];
        $crawler->filter('[data-entry]')->each(static function (Crawler $entry) use (&$flags): void {
            $flags[trim($entry->filter('[data-entry-note]')->text())] = null !== $entry->attr('data-entry-future');
        });

        return $flags;
    }
}

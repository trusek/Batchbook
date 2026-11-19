<?php

namespace App\Tests;

final class AddEntryTest extends AppWebTestCase
{
    public function testEntryWithCurrentTimeAppearsInJournal(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-03-15');
        $this->openBatch('Wino z aronii');

        $crawler = $this->addEntry('dodano 3 g pożywki');

        self::assertSame([
            ['15.03.2026 12:00', 'dodano 3 g pożywki'],
        ], $this->journal($crawler));
    }

    public function testFormDefaultsToClockTimeTruncatedToMinuteWithFocusOnNote(): void
    {
        $this->setNow('2026-03-18 09:41:37');
        $this->addBatch('Wino z aronii');

        $crawler = $this->openBatch('Wino z aronii');

        self::assertSame('2026-03-18T09:41', $crawler->filter('[name="entry[occurredAt]"]')->attr('value'));
        self::assertSame('datetime-local', $crawler->filter('[name="entry[occurredAt]"]')->attr('type'));
        self::assertNotNull($crawler->filter('textarea[name="entry[note]"]')->attr('autofocus'));
    }

    public function testBackdatedEntryLandsInChronologicalPlace(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-03-01');
        $this->openBatch('Wino z aronii');
        $this->addEntry('nastawiono', '2026-03-01T18:00');
        $this->addEntry('zlano znad osadu');

        $crawler = $this->addEntry('dodano cukier', '2026-03-08T12:00');

        self::assertSame([
            ['15.03.2026 12:00', 'zlano znad osadu'],
            ['08.03.2026 12:00', 'dodano cukier'],
            ['01.03.2026 18:00', 'nastawiono'],
        ], $this->journal($crawler));
    }

    public function testEntriesWithIdenticalTimeShowLaterAddedFirst(): void
    {
        $this->addBatch('Piwo pszeniczne', 'beer');
        $this->openBatch('Piwo pszeniczne');
        $this->addEntry('pierwszy', '2026-03-10T08:00');
        $this->setNow('2026-03-15 12:05');
        $this->addEntry('drugi', '2026-03-10T08:00');

        $crawler = $this->addEntry('trzeci', '2026-03-10T08:00');

        self::assertSame(['trzeci', 'drugi', 'pierwszy'], array_column($this->journal($crawler), 1));
    }

    public function testBatchWithoutStartDateAcceptsEntries(): void
    {
        $this->addBatch('Piwo bez daty', 'beer', '');
        $this->openBatch('Piwo bez daty');

        $crawler = $this->addEntry('zadano drożdże', '2026-03-14T20:15');

        self::assertSame([['14.03.2026 20:15', 'zadano drożdże']], $this->journal($crawler));
    }

    public function testBlankNoteIsRejectedWithErrorAtField(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');

        $crawler = $this->addEntry("  \n ");

        self::assertResponseStatusCodeSame(422);
        $noteRow = $crawler->filter('#entry_note')->ancestors()->first();
        self::assertStringContainsString('Wpisz notatkę.', $noteRow->text());
        self::assertSame([], $this->journal($this->openBatch('Wino z aronii')));
    }

    public function testMissingTimeIsRejected(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');

        $crawler = $this->addEntry('bez czasu', '');

        self::assertResponseStatusCodeSame(422);
        $timeRow = $crawler->filter('#entry_occurredAt')->ancestors()->first();
        self::assertStringContainsString('Podaj datę i godzinę wpisu.', $timeRow->text());
    }

    public function testNoteKeepsLineBreaks(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');

        $crawler = $this->addEntry("zlano do balonu\nBlg 6");

        self::assertMatchesRegularExpression('#zlano do balonu<br\s*/?>\s*Blg 6#', $crawler->filter('[data-entry-note]')->html());
    }

    public function testEntryTimeIsLocalWallClockAcrossDstChange(): void
    {
        $this->setNow('2026-04-01 10:00');
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $this->addEntry('przed zmianą czasu', '2026-03-28T22:00');

        $crawler = $this->addEntry('po zmianie czasu', '2026-03-29T22:00');

        self::assertSame([
            ['29.03.2026 22:00', 'po zmianie czasu'],
            ['28.03.2026 22:00', 'przed zmianą czasu'],
        ], $this->journal($crawler));
    }
}

<?php

namespace App\Tests;

use Symfony\Component\DomCrawler\Crawler;

final class AddBatchTest extends AppWebTestCase
{
    public function testNewBatchWithTodayStartDateAppearsOnList(): void
    {
        $crawler = $this->client->request('GET', '/');
        $this->client->click($crawler->selectLink('Dodaj nastaw')->link());
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Zapisz', [
            'batch[name]' => 'Wino z aronii',
            'batch[type]' => 'wine',
            'batch[startDate]' => '2026-03-15',
        ]);

        self::assertResponseRedirects('/');
        $crawler = $this->client->followRedirect();

        self::assertSame([
            ['Wino z aronii', 'Wino', '15.03.2026'],
        ], $this->batchRows($crawler));
    }

    public function testBatchWithoutStartDateCanBeAdded(): void
    {
        $this->addBatch('Piwo bez daty', 'beer', '');

        self::assertSame([
            ['Piwo bez daty', 'Piwo', ''],
        ], $this->batchRows($this->client->followRedirect()));
    }

    public function testBlankNameIsRejectedWithErrorAtField(): void
    {
        $this->addBatch('   ', 'wine', '2026-03-15');

        self::assertResponseStatusCodeSame(422);
        $nameRow = $this->client->getCrawler()->filter('#batch_name')->ancestors()->first();
        self::assertStringContainsString('Podaj nazwę nastawu.', $nameRow->text());

        $this->client->request('GET', '/');
        self::assertSelectorTextContains('main', 'Nie ma jeszcze żadnych nastawów');
    }

    public function testTwoBatchesCanHaveTheSameName(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2025-09-01');
        $this->addBatch('Wino z aronii', 'wine', '2026-03-15');

        self::assertSame([
            ['Wino z aronii', 'Wino', '15.03.2026'],
            ['Wino z aronii', 'Wino', '01.09.2025'],
        ], $this->batchRows($this->client->followRedirect()));
    }

    public function testListIsSortedNewestFirstByStartDateOrCreationDate(): void
    {
        $this->setNow('2026-03-01 10:00');
        $this->addBatch('Bez daty, utworzony 1 marca', 'beer', '');
        $this->addBatch('Start 10 marca (wstecz)', 'wine', '2026-03-10');
        $this->setNow('2026-03-20 10:00');
        $this->addBatch('Bez daty, utworzony 20 marca', 'wine', '');
        $this->addBatch('Start 5 lutego', 'wine', '2026-02-05');
        $this->addBatch('Start 15 marca', 'beer', '2026-03-15');

        $names = array_column($this->batchRows($this->client->followRedirect()), 0);

        self::assertSame([
            'Bez daty, utworzony 20 marca',
            'Start 15 marca',
            'Start 10 marca (wstecz)',
            'Bez daty, utworzony 1 marca',
            'Start 5 lutego',
        ], $names);
    }

    public function testFormOffersBatchTypesWithPolishLabels(): void
    {
        $crawler = $this->client->request('GET', '/nastawy/nowy');

        self::assertSame(
            ['Wino', 'Piwo'],
            $crawler->filter('select[name="batch[type]"] option')->each(static fn (Crawler $o): string => $o->text()),
        );
    }

    private function addBatch(string $name, string $type, string $startDate, string $description = ''): void
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
     * @return list<array{string, string, string}> nazwa, typ, data rozpoczęcia
     */
    private function batchRows(Crawler $crawler): array
    {
        return $crawler->filter('[data-batch]')->each(static fn (Crawler $row): array => [
            trim($row->filter('[data-batch-name]')->text()),
            trim($row->filter('[data-batch-type]')->text()),
            trim($row->filter('[data-batch-start-date]')->text()),
        ]);
    }
}

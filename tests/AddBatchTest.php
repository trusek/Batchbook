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

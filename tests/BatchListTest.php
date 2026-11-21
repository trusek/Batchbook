<?php

namespace App\Tests;

final class BatchListTest extends AppWebTestCase
{
    public function testEmptyListShowsMessageAndLinkToAddBatch(): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Nie ma jeszcze żadnych nastawów');
        self::assertCount(1, $crawler->selectLink('Dodaj nastaw'));
    }

    public function testLayoutIsPolishAndMobileFriendly(): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertSame('pl', $crawler->filter('html')->attr('lang'));
        self::assertSelectorExists('meta[name="viewport"][content*="width=device-width"]');
    }
}

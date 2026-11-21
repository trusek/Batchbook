<?php

namespace App\Tests;

final class BatchPageTest extends AppWebTestCase
{
    public function testBatchOnListLeadsToPageWithItsDetails(): void
    {
        $this->addBatch('Miód z lipy', 'wine', '2026-03-10', "Miód lipowy 3 kg\nWoda 6 l");
        $this->addBatch('Inny nastaw', 'beer');

        $this->openBatch('Miód z lipy');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Miód z lipy');
        self::assertSelectorTextContains('main [data-batch-type]', 'Wino');
        self::assertSelectorTextContains('main [data-batch-start-date]', '10.03.2026');
        $description = $this->client->getCrawler()->filter('[data-batch-description]')->html();
        self::assertMatchesRegularExpression('#Miód lipowy 3 kg<br\s*/?>\s*Woda 6 l#', $description);
        self::assertSelectorTextNotContains('main', 'Inny nastaw');
    }

    public function testUnknownBatchReturns404(): void
    {
        $this->client->request('GET', '/nastawy/999');

        self::assertResponseStatusCodeSame(404);
    }
}

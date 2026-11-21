<?php

namespace App\Tests;

final class EditBatchTest extends AppWebTestCase
{
    public function testEditingBatchChangesItsDataOnPageAndList(): void
    {
        $this->addBatch('Miód z lipy', 'wine', '2026-03-10', 'Stary opis');
        $this->openBatch('Miód z lipy');

        $this->client->clickLink('Edytuj nastaw');
        $this->client->submitForm('Zapisz', [
            'batch[name]' => 'Miód lipowy',
            'batch[type]' => 'beer',
            'batch[startDate]' => '2026-03-12',
            'batch[description]' => 'Nowy opis',
        ]);

        self::assertResponseStatusCodeSame(303);
        $this->client->followRedirect();
        self::assertSelectorTextContains('h1', 'Miód lipowy');
        self::assertSelectorTextContains('main [data-batch-type]', 'Piwo');
        self::assertSelectorTextContains('main [data-batch-start-date]', '12.03.2026');
        self::assertSelectorTextContains('main [data-batch-description]', 'Nowy opis');

        $list = $this->client->request('GET', '/');
        self::assertSame([['Miód lipowy', 'Piwo', '12.03.2026']], $this->batchRows($list));
    }

    public function testEditFormIsPrefilledWithCurrentData(): void
    {
        $this->addBatch('Miód z lipy', 'beer', '2026-03-10', 'Opis');
        $this->openBatch('Miód z lipy');

        $crawler = $this->client->clickLink('Edytuj nastaw');

        $values = $crawler->selectButton('Zapisz')->form()->getValues();
        self::assertSame('Miód z lipy', $values['batch[name]']);
        self::assertSame('beer', $values['batch[type]']);
        self::assertSame('2026-03-10', $values['batch[startDate]']);
        self::assertSame('Opis', $values['batch[description]']);
    }

    public function testEditRejectsBlankNameAndKeepsOldData(): void
    {
        $this->addBatch('Miód z lipy');
        $this->openBatch('Miód z lipy');

        $this->client->clickLink('Edytuj nastaw');
        $this->client->submitForm('Zapisz', ['batch[name]' => '   ']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form', 'Podaj nazwę nastawu.');

        $list = $this->client->request('GET', '/');
        self::assertSame('Miód z lipy', $this->batchRows($list)[0][0]);
    }
}

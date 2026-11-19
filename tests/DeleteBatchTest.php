<?php

namespace App\Tests;

final class DeleteBatchTest extends AppWebTestCase
{
    public function testDeletedBatchDisappearsFromList(): void
    {
        $this->addBatch('Pomyłka');
        $this->addBatch('Miód z lipy');
        $this->openBatch('Pomyłka');

        $this->client->submitForm('Usuń');

        self::assertResponseRedirects('/', 303);
        $list = $this->client->followRedirect();
        self::assertSame(['Miód z lipy'], array_column($this->batchRows($list), 0));
    }

    public function testDeleteButtonAsksForConfirmation(): void
    {
        $this->addBatch('Pomyłka');
        $crawler = $this->openBatch('Pomyłka');

        $form = $crawler->selectButton('Usuń')->form();

        self::assertSame('post', strtolower($form->getMethod()));
        $formNode = $form->getFormNode();
        self::assertStringContainsString('confirm', $formNode->getAttribute('data-controller'));
        self::assertNotSame('', $formNode->getAttribute('data-confirm-message-value'));
    }

    public function testDeletedBatchPageEditAndEntriesReturn404(): void
    {
        $this->addBatch('Pomyłka');
        $this->openBatch('Pomyłka');
        $this->addEntry('Wpis przed usunięciem');
        $batchUrl = $this->client->getRequest()->getUri();

        $this->client->submitForm('Usuń');

        $this->client->request('GET', $batchUrl);
        self::assertResponseStatusCodeSame(404);

        $this->client->request('GET', $batchUrl.'/edycja');
        self::assertResponseStatusCodeSame(404);

        $this->client->request('POST', $batchUrl.'/wpisy', ['entry' => ['note' => 'Po usunięciu']]);
        self::assertResponseStatusCodeSame(404);

        $this->client->request('POST', $batchUrl.'/usun');
        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteWithoutValidCsrfTokenIsRejected(): void
    {
        $this->addBatch('Miód z lipy');
        $this->openBatch('Miód z lipy');

        $this->client->submitForm('Usuń', ['_csrf_token' => 'zly-token']);

        self::assertResponseStatusCodeSame(403);
        $list = $this->client->request('GET', '/');
        self::assertSame(['Miód z lipy'], array_column($this->batchRows($list), 0));
    }

    public function testDeleteRequestWithoutTokenIsRejected(): void
    {
        $this->addBatch('Miód z lipy');
        $this->openBatch('Miód z lipy');
        $batchUrl = $this->client->getRequest()->getUri();

        $this->client->request('POST', $batchUrl.'/usun');

        self::assertResponseStatusCodeSame(403);
        $list = $this->client->request('GET', '/');
        self::assertSame(['Miód z lipy'], array_column($this->batchRows($list), 0));
    }
}

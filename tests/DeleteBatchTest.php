<?php

namespace App\Tests;

final class DeleteBatchTest extends AppWebTestCase
{
    public function testDeletedBatchDisappearsFromList(): void
    {
        $this->addBatch('Pomyłka');
        $this->addBatch('Miód z lipy');

        $this->deleteBatch('Pomyłka');

        self::assertResponseRedirects('/', 303);
        $list = $this->client->followRedirect();
        self::assertSame(['Miód z lipy'], array_column($this->batchRows($list), 0));
    }

    public function testDeleteIsOfferedOnlyAtTheBottomOfEditPage(): void
    {
        $this->addBatch('Pomyłka');
        $page = $this->openBatch('Pomyłka');

        self::assertCount(0, $page->filter('main form[action$="/usun"]'), 'Strona nastawu nie ma przycisków usuwania.');
        self::assertCount(0, $page->selectButton('Usuń nastaw'));

        $edit = $this->client->clickLink('Edytuj nastaw');

        self::assertCount(1, $edit->selectButton('Usuń nastaw'));
        $forms = $edit->filter('main form');
        self::assertStringEndsWith('/usun', (string) $forms->last()->attr('action'), 'Usuwanie jest na końcu strony edycji.');
    }

    public function testDeleteButtonAsksForConfirmation(): void
    {
        $this->addBatch('Pomyłka');
        $crawler = $this->openBatchEdit('Pomyłka');

        $form = $crawler->selectButton('Usuń nastaw')->form();

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

        $this->deleteBatch('Pomyłka');

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

        $this->deleteBatch('Miód z lipy', ['_csrf_token' => 'zly-token']);

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

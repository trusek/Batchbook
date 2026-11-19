<?php

namespace App\Tests;

final class DeleteEntryTest extends AppWebTestCase
{
    /**
     * Scenariusz 5 (Poprawka): gdy edytuję lub usunę wpis, wtedy dziennik się aktualizuje, a usunięty wpis znika.
     */
    public function testEditingAndDeletingEntriesUpdatesJournal(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-03-01');
        $this->openBatch('Wino z aronii');
        $this->addEntry('nastawiono', '2026-03-01T18:00');
        $this->addEntry('pomyłka', '2026-03-10T08:00');
        $this->addEntry('dodano cukier', '2026-03-14T12:00');

        $this->editEntry('dodano cukier', ['entry_edit[occurredAt]' => '2026-03-05T12:00']);
        $this->deleteEntry('pomyłka');

        self::assertResponseRedirects(null, 303);
        self::assertSame([
            ['05.03.2026 12:00', 'dodano cukier'],
            ['01.03.2026 18:00', 'nastawiono'],
        ], $this->journal($this->client->followRedirect()));
    }

    public function testDeleteButtonAsksForConfirmationAndRefreshesJournal(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $page = $this->addEntry('pomyłka');

        $form = $this->entryWithNote($page, 'pomyłka')->selectButton('Usuń')->form();

        self::assertSame('post', strtolower($form->getMethod()));
        $formNode = $form->getFormNode();
        self::assertStringContainsString('confirm', $formNode->getAttribute('data-controller'));
        self::assertNotSame('', $formNode->getAttribute('data-confirm-message-value'));
        self::assertCount(1, $page->filter(sprintf('turbo-frame[id="%s"] [data-entry]', $formNode->getAttribute('data-turbo-frame'))));
    }

    public function testDeletedEntryDoesNotCountTowardLastEntryDate(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-03-01');
        $this->addBatch('Miód z lipy', 'wine', '2026-02-01');
        $this->openBatch('Wino z aronii');
        $this->addEntry('nastawiono', '2026-03-01T18:00');
        $this->addEntry('pomyłka', '2026-03-14T08:00');
        $this->openBatch('Miód z lipy');
        $this->addEntry('jedyny wpis, pomyłka', '2026-03-02T10:00');

        $this->deleteEntry('jedyny wpis, pomyłka');
        $this->openBatch('Wino z aronii');
        $this->deleteEntry('pomyłka');

        $list = $this->client->request('GET', '/');
        self::assertSame(['Wino z aronii' => '01.03.2026 18:00', 'Miód z lipy' => ''], $this->lastEntryDates($list));
    }

    public function testDeleteWithoutValidCsrfTokenIsRejected(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $this->addEntry('ważny wpis');

        $this->deleteEntry('ważny wpis', ['_csrf_token' => 'zly-token']);

        self::assertResponseStatusCodeSame(403);
        self::assertSame(['ważny wpis'], array_column($this->journal($this->openBatch('Wino z aronii')), 1));
    }

    public function testDeleteRequestWithoutTokenIsRejected(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $page = $this->addEntry('ważny wpis');
        $deleteUrl = $this->entryWithNote($page, 'ważny wpis')->selectButton('Usuń')->form()->getUri();

        $this->client->request('POST', $deleteUrl);

        self::assertResponseStatusCodeSame(403);
        self::assertSame(['ważny wpis'], array_column($this->journal($this->openBatch('Wino z aronii')), 1));
    }

    public function testEditingOrDeletingDeletedEntryReturns404(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $page = $this->addEntry('pomyłka');
        $entry = $this->entryWithNote($page, 'pomyłka');
        $editUrl = $entry->selectLink('Edytuj')->link()->getUri();
        $deleteForm = $entry->selectButton('Usuń')->form();

        $this->deleteEntry('pomyłka');

        $this->client->request('GET', $editUrl);
        self::assertResponseStatusCodeSame(404);
        $this->client->request('POST', $editUrl, ['entry_edit' => ['note' => 'poprawka']]);
        self::assertResponseStatusCodeSame(404);
        $this->client->submit($deleteForm);
        self::assertResponseStatusCodeSame(404);
    }

    public function testDeletingEntryOfDeletedBatchReturns404(): void
    {
        $this->addBatch('Pomyłka');
        $this->openBatch('Pomyłka');
        $page = $this->addEntry('wpis');
        $deleteForm = $this->entryWithNote($page, 'wpis')->selectButton('Usuń')->form();

        $this->client->submitForm('Usuń');

        $this->client->submit($deleteForm);
        self::assertResponseStatusCodeSame(404);
    }
}

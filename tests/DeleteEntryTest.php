<?php

namespace App\Tests;

use Symfony\Component\DomCrawler\Form;

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

    public function testDeleteIsOfferedOnlyInEntryEditNextToSaveAndCancel(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $page = $this->addEntry('pomyłka');

        self::assertCount(0, $page->filter('main form[action$="/usun"]'), 'Wiersz dziennika nie ma przycisku usuwania.');

        $edit = $this->openEntryEdit('pomyłka');
        $frameId = $this->entryWithNote($page, 'pomyłka')->closest('turbo-frame')?->attr('id');
        $frame = $edit->filter(sprintf('turbo-frame[id="%s"]', $frameId));

        self::assertCount(1, $frame->selectButton('Usuń wpis'));
        self::assertCount(1, $frame->selectButton('Zapisz'));
        self::assertCount(1, $frame->selectLink('Anuluj'));
    }

    public function testDeleteButtonAsksForConfirmationAndRefreshesJournal(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $page = $this->addEntry('pomyłka');

        $form = $this->entryDeleteForm('pomyłka');

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
        $this->addEntry('ważny wpis');
        $deleteUrl = $this->entryDeleteForm('ważny wpis')->getUri();

        $this->client->request('POST', $deleteUrl);

        self::assertResponseStatusCodeSame(403);
        self::assertSame(['ważny wpis'], array_column($this->journal($this->openBatch('Wino z aronii')), 1));
    }

    public function testEditingOrDeletingDeletedEntryReturns404(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $page = $this->addEntry('pomyłka');
        $editUrl = $this->entryWithNote($page, 'pomyłka')->selectLink('Edytuj wpis')->link()->getUri();
        $deleteForm = $this->entryDeleteForm('pomyłka');

        $this->client->submit($deleteForm);

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
        $this->addEntry('wpis');
        $deleteForm = $this->entryDeleteForm('wpis');

        $this->deleteBatch('Pomyłka');

        $this->client->submit($deleteForm);
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * Formularz „Usuń wpis” z edycji wpisu o podanej notatce (na aktualnie otwartej stronie nastawu).
     */
    private function entryDeleteForm(string $note): Form
    {
        return $this->openEntryEdit($note)->selectButton('Usuń wpis')->form();
    }
}

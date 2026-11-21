<?php

namespace App\Tests;

use Symfony\Component\DomCrawler\Field\FormField;

final class EditEntryTest extends AppWebTestCase
{
    public function testEditedEntryMovesToItsPlaceInJournal(): void
    {
        $this->addBatch('Wino z aronii', 'wine', '2026-03-01');
        $this->openBatch('Wino z aronii');
        $this->addEntry('nastawiono', '2026-03-01T18:00');
        $this->addEntry('zlano znad osadu', '2026-03-14T10:00');
        $this->addEntry('dodano cukier', '2026-03-08T12:00');

        $crawler = $this->editEntry('dodano cukier', [
            'entry_edit[occurredAt]' => '2026-03-14T19:30',
            'entry_edit[note]' => 'dodano 200 g cukru',
        ]);

        self::assertSame([
            ['14.03.2026 19:30', 'dodano 200 g cukru'],
            ['14.03.2026 10:00', 'zlano znad osadu'],
            ['01.03.2026 18:00', 'nastawiono'],
        ], $this->journal($crawler));
    }

    public function testEditReplacesEntryWithFormInItsTurboFrameAndSaveRefreshesJournal(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $page = $this->addEntry('zlano znad osadu', '2026-03-14T10:00');
        $entryFrame = $this->entryWithNote($page, 'zlano znad osadu')->closest('turbo-frame');
        self::assertNotNull($entryFrame);
        $frameId = $entryFrame->attr('id');

        $response = $this->openEntryEdit('zlano znad osadu');

        self::assertResponseIsSuccessful();
        $form = $response->filter(sprintf('turbo-frame[id="%s"] form[name="entry_edit"]', $frameId));
        self::assertCount(1, $form, 'Formularz edycji powinien być w ramce wpisu.');
        self::assertSame('2026-03-14T10:00', $form->filter('[name="entry_edit[occurredAt]"]')->attr('value'));
        self::assertSame('zlano znad osadu', $form->filter('[name="entry_edit[note]"]')->text());
        $target = $form->attr('data-turbo-frame');
        self::assertNotNull($target, 'Zapis powinien odświeżać ramkę dziennika.');
        self::assertCount(1, $page->filter(sprintf('turbo-frame[id="%s"] [data-entry]', $target)));
    }

    /**
     * Pełna strona edycji (bez JS lub po błędzie walidacji) ma jeden autofocus: w notatce edytowanego wpisu,
     * a nie w formularzu szybkiego wpisu.
     */
    public function testEditPageFocusesOnlyEditedNote(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $this->addEntry('zlano znad osadu', '2026-03-14T10:00');

        $page = $this->openEntryEdit('zlano znad osadu');

        $focused = $page->filter('[autofocus]');
        self::assertCount(1, $focused);
        self::assertSame('entry_edit[note]', $focused->attr('name'));

        $page = $this->client->submitForm('Zapisz', ['entry_edit[note]' => '']);

        self::assertResponseStatusCodeSame(422);
        $focused = $page->filter('[autofocus]');
        self::assertCount(1, $focused);
        self::assertSame('entry_edit[note]', $focused->attr('name'));
    }

    public function testChangingOnlyDateKeepsTime(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $this->addEntry('dodano pożywkę', '2026-03-10T21:45');

        $form = $this->openEntryEdit('dodano pożywkę')->selectButton('Zapisz')->form();
        $occurredAt = $form->get('entry_edit[occurredAt]');
        self::assertInstanceOf(FormField::class, $occurredAt);
        $value = $occurredAt->getValue();
        self::assertIsString($value);
        $changedDateOnly = str_replace('2026-03-10', '2026-03-11', $value);
        $this->client->submit($form, ['entry_edit[occurredAt]' => $changedDateOnly]);

        self::assertSame([['11.03.2026 21:45', 'dodano pożywkę']], $this->journal($this->client->followRedirect()));
    }

    public function testBlankNoteIsRejectedWithErrorAtField(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $this->addEntry('dodano pożywkę', '2026-03-10T21:45');

        $crawler = $this->editEntry('dodano pożywkę', ['entry_edit[note]' => " \n "]);

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('Wpisz notatkę.', $crawler->filter('#entry_edit_note')->ancestors()->first()->text());
        self::assertSame([['10.03.2026 21:45', 'dodano pożywkę']], $this->journal($this->openBatch('Wino z aronii')));
    }

    public function testMissingTimeIsRejectedWithErrorAtField(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $this->addEntry('dodano pożywkę', '2026-03-10T21:45');

        $crawler = $this->editEntry('dodano pożywkę', ['entry_edit[occurredAt]' => '']);

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('Podaj datę i godzinę wpisu.', $crawler->filter('#entry_edit_occurredAt')->ancestors()->first()->text());
        self::assertSame([['10.03.2026 21:45', 'dodano pożywkę']], $this->journal($this->openBatch('Wino z aronii')));
    }

    public function testEditingEntryOfDeletedBatchReturns404(): void
    {
        $this->addBatch('Pomyłka');
        $this->openBatch('Pomyłka');
        $this->addEntry('wpis');
        $editUrl = $this->entryWithNote($this->client->getCrawler(), 'wpis')->selectLink('Edytuj wpis')->link()->getUri();

        $this->deleteBatch('Pomyłka');

        $this->client->request('GET', $editUrl);
        self::assertResponseStatusCodeSame(404);
        $this->client->request('POST', $editUrl, ['entry_edit' => ['note' => 'po usunięciu']]);
        self::assertResponseStatusCodeSame(404);
    }
}

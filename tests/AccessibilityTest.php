<?php

namespace App\Tests;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Wymagania WCAG 2.2 AA sprawdzalne w HTML-u: etykiety pól, powiązanie błędów z polami,
 * link pomijający pasek boczny, nazwy dostępne ikon.
 */
final class AccessibilityTest extends AppWebTestCase
{
    public function testEveryFormFieldHasLabel(): void
    {
        $this->addBatch('Wino z aronii');
        $pages = ['nowy nastaw' => $this->client->request('GET', '/nastawy/nowy')];
        $this->openBatch('Wino z aronii');
        $pages['strona nastawu'] = $this->addEntry('nastawiono');
        $pages['edycja wpisu'] = $this->openEntryEdit('nastawiono');
        $pages['edycja nastawu'] = $this->openBatchEdit('Wino z aronii');

        foreach ($pages as $name => $page) {
            $fields = $page->filter('input:not([type="hidden"]), select, textarea');
            self::assertGreaterThan(0, $fields->count(), $name);
            $fields->each(function (Crawler $field) use ($page, $name): void {
                $label = $page->filter(sprintf('label[for="%s"]', $field->attr('id')));
                self::assertCount(1, $label, sprintf('%s: pole %s bez etykiety', $name, $field->attr('name')));
                self::assertNotSame('', trim($label->text()));
            });
        }
    }

    public function testQuickEntryFieldsHaveVisuallyHiddenLabels(): void
    {
        $this->addBatch('Wino z aronii');

        $page = $this->openBatch('Wino z aronii');

        self::assertSame('Notatka', trim($page->filter('label[for="entry_note"]')->text()));
        self::assertStringContainsString('sr-only', (string) $page->filter('label[for="entry_note"]')->attr('class'));
        self::assertSame('Data i godzina wpisu', trim($page->filter('label[for="entry_occurredAt"]')->text()));
    }

    public function testInvalidFieldsAreMarkedAndDescribedByTheirErrors(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');
        $page = $this->addEntry(' ', '');

        self::assertResponseStatusCodeSame(422);
        $this->assertInvalidWithError($page, 'entry_note', 'Wpisz notatkę.');
        $this->assertInvalidWithError($page, 'entry_occurredAt', 'Podaj datę i godzinę wpisu.');

        $this->openBatch('Wino z aronii');
        $this->addEntry('nastawiono');
        $page = $this->editEntry('nastawiono', ['entry_edit[note]' => '']);
        self::assertResponseStatusCodeSame(422);
        $this->assertInvalidWithError($page, 'entry_edit_note', 'Wpisz notatkę.');
        self::assertNull($page->filter('#entry_edit_occurredAt')->attr('aria-invalid'));

        $this->addBatch(' ');
        self::assertResponseStatusCodeSame(422);
        $this->assertInvalidWithError($this->client->getCrawler(), 'batch_name', 'Podaj nazwę nastawu.');
    }

    public function testSkipLinkPrecedesSidebarAndTargetsMainContent(): void
    {
        $page = $this->client->request('GET', '/');

        $firstLink = $page->filter('body a')->first();
        self::assertSame('Przejdź do treści', trim($firstLink->text()));
        $target = substr((string) $firstLink->attr('href'), 1);
        self::assertCount(1, $page->filter(sprintf('main[id="%s"]', $target)));
    }

    public function testEditIconsHaveAccessibleNames(): void
    {
        $this->addBatch('Wino z aronii');
        $this->openBatch('Wino z aronii');

        $page = $this->addEntry('zlano znad osadu', '2026-03-14T10:00');

        self::assertCount(1, $page->filter('main h1')->selectLink('Edytuj nastaw'));
        self::assertCount(1, $this->entryWithNote($page, 'zlano znad osadu')->selectLink('Edytuj wpis z 14.03.2026 10:00'));
        self::assertCount(0, $page->filter('main svg:not([aria-hidden="true"])'), 'Ikony są ozdobne.');
    }

    private function assertInvalidWithError(Crawler $page, string $fieldId, string $message): void
    {
        $field = $page->filter('#'.$fieldId);
        self::assertSame('true', $field->attr('aria-invalid'), $fieldId);
        $describedBy = array_filter(explode(' ', (string) $field->attr('aria-describedby')));
        self::assertNotEmpty($describedBy, $fieldId);
        $descriptions = implode(' ', array_map(static fn (string $id): string => $page->filter('#'.$id)->text(), $describedBy));
        self::assertStringContainsString($message, $descriptions);
    }
}

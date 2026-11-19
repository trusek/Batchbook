<?php

namespace App\Controller;

use App\Entity\Batch;
use App\Entity\Entry;
use App\Form\EntryFormType;
use App\Repository\EntryRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Strona nastawu (batch/show.html.twig): nastaw, formularz szybkiego wpisu i dziennik,
 * opcjonalnie z jednym wpisem zamienionym na formularz edycji.
 * Wspólna dla wyświetlenia nastawu, błędu szybkiego wpisu i edycji wpisu.
 */
final class BatchPage
{
    public const TEMPLATE = 'batch/show.html.twig';

    public function __construct(
        private readonly EntryRepository $entries,
        private readonly ClockInterface $clock,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urls,
    ) {
    }

    /**
     * Formularz szybkiego wpisu (entry[...]) z czasem ustawionym na „teraz”.
     *
     * @param bool $focusNote autofocus w notatce; wyłączony, gdy na stronie jest formularz edycji
     *
     * @return FormInterface<Entry>
     */
    public function newEntryForm(Batch $batch, bool $focusNote = true): FormInterface
    {
        $entry = new Entry($batch, $this->clock->now());
        $entry->setOccurredAt($this->clock->now());

        return $this->formFactory->create(EntryFormType::class, $entry, [
            'action' => $this->urls->generate('entry_new', ['batchId' => $batch->getId()]),
            'focus_note' => $focusNote,
        ]);
    }

    /**
     * Parametry szablonu strony nastawu.
     *
     * @param FormInterface<Entry>|null $entryForm formularz szybkiego wpisu (domyślnie nowy, pusty)
     * @param FormInterface<Entry>|null $editForm  formularz edycji wpisu, wyświetlany w miejscu tego wpisu
     *
     * @return array<string, mixed>
     */
    public function parameters(Batch $batch, ?FormInterface $entryForm = null, ?FormInterface $editForm = null): array
    {
        $parameters = [
            'batch' => $batch,
            'entries' => $this->entries->findJournal($batch),
            'entry_form' => $entryForm ?? $this->newEntryForm($batch, focusNote: null === $editForm),
        ];

        if (null !== $editForm) {
            $parameters['edited_entry'] = $editForm->getData();
            $parameters['edit_form'] = $editForm;
        }

        return $parameters;
    }
}

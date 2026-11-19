<?php

namespace App\Controller;

use App\Form\EntryFormType;
use App\Repository\BatchRepository;
use App\Repository\EntryRepository;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EntryController extends AbstractController
{
    public function __construct(
        private readonly BatchRepository $batches,
        private readonly EntryRepository $entries,
        private readonly ClockInterface $clock,
        private readonly FormFactoryInterface $formFactory,
        private readonly BatchPage $page,
    ) {
    }

    /**
     * Szybki wpis ze strony nastawu. Sukces: 303 na stronę nastawu;
     * błąd walidacji: strona nastawu z błędami przy polach (422).
     */
    #[Route('/nastawy/{batchId}/wpisy', name: 'entry_new', requirements: ['batchId' => '\d+'], methods: ['POST'])]
    public function new(int $batchId, Request $request): Response
    {
        $batch = $this->batches->findVisible($batchId) ?? throw $this->createNotFoundException();

        $form = $this->page->newEntryForm($batch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entries->save($form->getData());

            return $this->redirectToRoute('batch_show', ['id' => $batchId], Response::HTTP_SEE_OTHER);
        }

        // render() ustawia 422 dla nieprawidłowego formularza (wymóg Turbo).
        return $this->render(BatchPage::TEMPLATE, $this->page->parameters($batch, entryForm: $form));
    }

    /**
     * Edycja wpisu w miejscu. Odpowiedź to strona nastawu, w której dziennik ma ten wpis
     * zamieniony na formularz: Turbo wycina z niej ramkę wpisu ("entry-<id>"), a bez JS
     * działa jak zwykła strona. Formularz wysyła się do ramki "journal", więc po zapisie
     * (303 na stronę nastawu) odświeża się cały dziennik i wpis trafia na właściwe miejsce.
     * Błąd walidacji: ta sama strona z błędami przy polach (422).
     */
    #[Route('/wpisy/{id}/edycja', name: 'entry_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $entry = $this->entries->findVisible($id) ?? throw $this->createNotFoundException();
        $batch = $entry->getBatch();

        // Osobna nazwa formularza (entry_edit[...]), żeby pola nie kolidowały z szybkim wpisem na tej samej stronie.
        $form = $this->formFactory->createNamed('entry_edit', EntryFormType::class, $entry, [
            'action' => $this->generateUrl('entry_edit', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entries->save($entry);

            return $this->redirectToRoute('batch_show', ['id' => $batch->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render(BatchPage::TEMPLATE, $this->page->parameters($batch, editForm: $form));
    }

    /**
     * Miękkie usunięcie wpisu. Formularz z templates/_delete_form.html.twig (potwierdzenie + token CSRF).
     */
    #[Route('/wpisy/{id}/usun', name: 'entry_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request, DeleteForm $deleteForm): Response
    {
        $entry = $this->entries->findVisible($id) ?? throw $this->createNotFoundException();
        $deleteForm->denyUnlessValid($request);

        $entry->markDeleted($this->clock->now());
        $this->entries->save($entry);

        return $this->redirectToRoute('batch_show', ['id' => $entry->getBatch()->getId()], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\Entry;
use App\Form\EntryFormType;
use App\Repository\BatchRepository;
use App\Repository\EntryRepository;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EntryController extends AbstractController
{
    public function __construct(
        private readonly BatchRepository $batches,
        private readonly EntryRepository $entries,
        private readonly ClockInterface $clock,
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

        $entry = new Entry($batch, $this->clock->now());
        $form = $this->createForm(EntryFormType::class, $entry, [
            'action' => $this->generateUrl('entry_new', ['batchId' => $batchId]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entries->add($entry);

            return $this->redirectToRoute('batch_show', ['id' => $batchId], Response::HTTP_SEE_OTHER);
        }

        // render() ustawia 422 dla nieprawidłowego formularza (wymóg Turbo).
        return $this->render('batch/show.html.twig', [
            'batch' => $batch,
            'entries' => $this->entries->findJournal($batch),
            'entry_form' => $form,
        ]);
    }
}

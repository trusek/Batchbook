<?php

namespace App\Controller;

use App\Entity\Batch;
use App\Entity\Entry;
use App\Form\BatchFormType;
use App\Form\EntryFormType;
use App\Repository\BatchRepository;
use App\Repository\EntryRepository;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BatchController extends AbstractController
{
    public function __construct(
        private readonly BatchRepository $batches,
        private readonly ClockInterface $clock,
    ) {
    }

    #[Route('/', name: 'batch_index', methods: ['GET'])]
    public function index(EntryRepository $entries): Response
    {
        return $this->render('batch/index.html.twig', [
            'batches' => $this->batches->findForList(),
            'last_entry_dates' => $entries->findLastEntryDates($this->clock->now()),
        ]);
    }

    #[Route('/nastawy/nowy', name: 'batch_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $batch = new Batch($this->clock->now());
        $form = $this->createForm(BatchFormType::class, $batch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->batches->add($batch);

            return $this->redirectToRoute('batch_index', status: Response::HTTP_SEE_OTHER);
        }

        // render() ustawia 422 dla nieprawidłowego formularza (wymóg Turbo).
        return $this->render('batch/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Strona nastawu: dane nastawu, formularz szybkiego wpisu (czas domyślnie „teraz”) i dziennik.
     * Formularz wysyła się do EntryController::new.
     */
    #[Route('/nastawy/{id}', name: 'batch_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, EntryRepository $entries): Response
    {
        $batch = $this->batches->findVisible($id) ?? throw $this->createNotFoundException();

        $entry = new Entry($batch, $this->clock->now());
        $entry->setOccurredAt($this->clock->now());

        return $this->render('batch/show.html.twig', [
            'batch' => $batch,
            'entries' => $entries->findJournal($batch),
            'entry_form' => $this->createForm(EntryFormType::class, $entry, [
                'action' => $this->generateUrl('entry_new', ['batchId' => $batch->getId()]),
            ]),
        ]);
    }
}

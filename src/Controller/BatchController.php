<?php

namespace App\Controller;

use App\Entity\Batch;
use App\Form\BatchFormType;
use App\Repository\BatchRepository;
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
    public function index(): Response
    {
        return $this->render('batch/index.html.twig', [
            'batches' => $this->batches->findForList(),
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
}

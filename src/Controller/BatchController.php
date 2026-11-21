<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BatchController extends AbstractController
{
    #[Route('/', name: 'batch_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('batch/index.html.twig', [
            'batches' => [],
        ]);
    }
}

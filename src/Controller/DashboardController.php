<?php

namespace App\Controller;

use App\Repository\BatchRepository;
use App\Repository\EntryRepository;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Ekran startowy (pulpit): najbliższe plany i ostatnia aktywność ze wszystkich nastawów.
 * Lista nastawów jest w pasku bocznym (templates/_sidebar.html.twig).
 */
final class DashboardController extends AbstractController
{
    private const UPCOMING_LIMIT = 10;
    private const RECENT_LIMIT = 15;

    #[Route('/', name: 'dashboard', methods: ['GET'])]
    public function index(BatchRepository $batches, EntryRepository $entries, ClockInterface $clock): Response
    {
        $now = $clock->now();

        return $this->render('dashboard/index.html.twig', [
            'has_batches' => $batches->count(['deletedAt' => null]) > 0,
            'upcoming' => $entries->findUpcoming($now, self::UPCOMING_LIMIT),
            'recent' => $entries->findRecent($now, self::RECENT_LIMIT),
        ]);
    }
}

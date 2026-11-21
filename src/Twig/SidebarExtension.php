<?php

namespace App\Twig;

use App\Sidebar\Sidebar;
use App\Sidebar\SidebarBatch;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Funkcja `sidebar_batches()` dla paska bocznego w templates/_sidebar.html.twig,
 * żeby kontrolery nie musiały przekazywać listy nastawów do każdego widoku.
 */
final class SidebarExtension extends AbstractExtension
{
    public function __construct(private readonly Sidebar $sidebar)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sidebar_batches', $this->batches(...)),
        ];
    }

    /**
     * @return list<SidebarBatch>
     */
    public function batches(): array
    {
        return $this->sidebar->batches();
    }
}

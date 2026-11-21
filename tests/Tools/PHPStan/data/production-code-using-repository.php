<?php

namespace App\Controller;

use App\Repository\BatchRepository;
use Doctrine\ORM\EntityManagerInterface;

final class UsesRepository
{
    public function __construct(BatchRepository $batches, EntityManagerInterface $em)
    {
    }
}

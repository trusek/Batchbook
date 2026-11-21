<?php

namespace App\Tests\Fixture;

use App\Entity\Batch;
use App\Repository\BatchRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BypassingHttpTest extends WebTestCase
{
    public function testReadsDatabase(): void
    {
        static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
    }
}

<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

/**
 * Baza testów funkcjonalnych: wszystko przez HTTP, zegar zamrożony.
 *
 * - Każdy test dostaje świeżego klienta ($this->client).
 * - Zegar (ClockInterface) jest zamrożony na DEFAULT_NOW; zmień go przez
 *   $this->setNow('2026-03-18 09:30'). Czas jest w strefie Europe/Warsaw.
 * - Baza testowa jest tworzona z migracji raz na przebieg (tests/bootstrap.php),
 *   a DAMA DoctrineTestBundle cofa zmiany po każdym teście, więc każdy test
 *   startuje na pustej bazie.
 */
abstract class AppWebTestCase extends WebTestCase
{
    use ClockSensitiveTrait;

    protected const DEFAULT_NOW = '2026-03-15 12:00:00';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->setNow(self::DEFAULT_NOW);
    }

    /**
     * Ustawia bieżący czas widziany przez aplikację (lokalny czas Europe/Warsaw).
     */
    protected function setNow(string $localDateTime): void
    {
        static::mockTime(new \DateTimeImmutable($localDateTime, new \DateTimeZone('Europe/Warsaw')));
    }
}

<?php

namespace App\Fixture;

use DateTimeImmutable;

function now(int $timestamp, string $given): void
{
    time();
    \strtotime('+1 day');
    date('Y-m-d');
    date('Y-m-d', $timestamp);
    gmdate('H:i');
    gmdate('H:i', $timestamp);
    new \DateTime();
    new DateTimeImmutable('now');
    new \DateTimeImmutable(' NOW ', new \DateTimeZone('UTC'));
    new DateTimeImmutable('2026-03-15 12:00');
    new DateTimeImmutable($given);
    date_create();
    date_create_immutable('now');
    date_create_immutable('2026-03-15');
    new DateTimeImmutable(timezone: new \DateTimeZone('UTC'));
    new DateTimeImmutable(null);
    new DateTimeImmutable('');
    new DateTimeImmutable(datetime: '2026-03-15');
}

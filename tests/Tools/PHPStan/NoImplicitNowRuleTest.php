<?php

namespace App\Tests\Tools\PHPStan;

use App\Tools\PHPStan\NoImplicitNowRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoImplicitNowRule>
 */
final class NoImplicitNowRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoImplicitNowRule();
    }

    public function testReportsReadingCurrentTimeOutsideClock(): void
    {
        $this->analyse([__DIR__.'/data/implicit-now.php'], [
            ['„Teraz” bierz z ClockInterface, nie z time().', 9],
            ['„Teraz” bierz z ClockInterface, nie z strtotime().', 10],
            ['„Teraz” bierz z ClockInterface, nie z date() bez znacznika czasu.', 11],
            ['„Teraz” bierz z ClockInterface, nie z gmdate() bez znacznika czasu.', 13],
            ['„Teraz” bierz z ClockInterface, nie z new DateTime() bez daty.', 15],
            ['„Teraz” bierz z ClockInterface, nie z new DateTimeImmutable() bez daty.', 16],
            ['„Teraz” bierz z ClockInterface, nie z new DateTimeImmutable() bez daty.', 17],
            ['„Teraz” bierz z ClockInterface, nie z date_create() bez daty.', 20],
            ['„Teraz” bierz z ClockInterface, nie z date_create_immutable() bez daty.', 21],
            ['„Teraz” bierz z ClockInterface, nie z new DateTimeImmutable() bez daty.', 23],
            ['„Teraz” bierz z ClockInterface, nie z new DateTimeImmutable() bez daty.', 24],
            ['„Teraz” bierz z ClockInterface, nie z new DateTimeImmutable() bez daty.', 25],
        ]);
    }
}

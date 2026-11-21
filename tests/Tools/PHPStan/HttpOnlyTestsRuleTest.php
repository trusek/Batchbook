<?php

namespace App\Tests\Tools\PHPStan;

use App\Tools\PHPStan\HttpOnlyTestsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<HttpOnlyTestsRule>
 */
final class HttpOnlyTestsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new HttpOnlyTestsRule();
    }

    public function testReportsTestReachingPastHttpToDatabase(): void
    {
        $this->analyse([__DIR__.'/data/test-bypassing-http.php'], [
            ['Testy tylko przez HTTP: nie używaj App\Repository\BatchRepository.', 6],
            ['Testy tylko przez HTTP: nie używaj Doctrine\DBAL\Connection.', 7],
            ['Testy tylko przez HTTP: nie używaj Doctrine\ORM\EntityManagerInterface.', 8],
            ['Testy tylko przez HTTP: nie używaj Doctrine\ORM\EntityManagerInterface.', 15],
        ]);
    }

    public function testIgnoresProductionCode(): void
    {
        $this->analyse([__DIR__.'/data/production-code-using-repository.php'], []);
    }
}

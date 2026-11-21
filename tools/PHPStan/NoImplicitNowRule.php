<?php

namespace App\Tools\PHPStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * „Teraz” wyłącznie z ClockInterface: zakazuje funkcji i konstruktorów,
 * które same odczytują bieżący czas systemu.
 *
 * Zakazane: time(), strtotime(), date()/gmdate() bez znacznika czasu,
 * new DateTime/DateTimeImmutable i date_create(_immutable)() bez daty, z null, '' albo 'now'.
 *
 * @implements Rule<Node\Expr\CallLike>
 */
final class NoImplicitNowRule implements Rule
{
    private const ALWAYS_FORBIDDEN =['time', 'strtotime'];
    private const WITHOUT_TIMESTAMP = ['date', 'gmdate'];
    private const WITHOUT_DATE_FUNCTIONS = ['date_create', 'date_create_immutable'];
    private const WITHOUT_DATE_CLASSES = ['datetime', 'datetimeimmutable'];

    public function getNodeType(): string
    {
        return Node\Expr\CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->isFirstClassCallable()) {
            return [];
        }

        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            $function = strtolower($node->name->toString());

            return match (true) {
                in_array($function, self::ALWAYS_FORBIDDEN, true) => $this->error(sprintf('%s()', $function)),
                in_array($function, self::WITHOUT_TIMESTAMP, true) && count($node->getArgs()) < 2 => $this->error(sprintf('%s() bez znacznika czasu', $function)),
                in_array($function, self::WITHOUT_DATE_FUNCTIONS, true) && $this->readsNow($node, $scope) => $this->error(sprintf('%s() bez daty', $function)),
                default => [],
            };
        }

        if ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
            $class = $node->class->toString();

            if (in_array(strtolower($class), self::WITHOUT_DATE_CLASSES, true) && $this->readsNow($node, $scope)) {
                return $this->error(sprintf('new %s() bez daty', $class));
            }
        }

        return [];
    }

    /**
     * Argument z datą (pierwszy albo nazwany `datetime`) pominięty, null, pusty albo 'now'.
     */
    private function readsNow(Node\Expr\FuncCall|Node\Expr\New_ $call, Scope $scope): bool
    {
        $date = null;
        foreach ($call->getArgs() as $position => $arg) {
            if ((0 === $position && null === $arg->name) || 'datetime' === $arg->name?->toString()) {
                $date = $arg->value;
                break;
            }
        }

        if (null === $date) {
            return true;
        }

        $type = $scope->getType($date);
        if ($type->isNull()->yes()) {
            return true;
        }

        foreach ($type->getConstantStrings() as $string) {
            if (in_array(strtolower(trim($string->getValue())), ['', 'now'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function error(string $source): array
    {
        return [
            RuleErrorBuilder::message(sprintf('„Teraz” bierz z ClockInterface, nie z %s.', $source))
                ->identifier('app.implicitNow')
                ->build(),
        ];
    }
}

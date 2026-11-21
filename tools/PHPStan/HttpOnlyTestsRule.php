<?php

namespace App\Tools\PHPStan;

use PhpParser\Node;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Testy (przestrzeń App\Tests) sprawdzają aplikację wyłącznie przez HTTP:
 * nie sięgają do repozytoriów ani bezpośrednio do bazy.
 *
 * Łapie każde odwołanie do zakazanej klasy: import (use), ::class, typ, new.
 *
 * @implements Rule<FileNode>
 */
final class HttpOnlyTestsRule implements Rule
{
    private const TESTS_NAMESPACE = 'App\Tests';

    private const FORBIDDEN_PREFIXES = ['App\Repository\\'];

    private const FORBIDDEN_CLASSES = [
        'Doctrine\DBAL\Connection',
        'Doctrine\ORM\EntityManager',
        'Doctrine\ORM\EntityManagerInterface',
        'Doctrine\Persistence\ManagerRegistry',
        'Doctrine\Persistence\ObjectManager',
    ];

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $finder = new NodeFinder();
        $errors = [];

        /** @var list<Node\Stmt\Namespace_> $namespaces */
        $namespaces = $finder->findInstanceOf($node->getNodes(), Node\Stmt\Namespace_::class);
        foreach ($namespaces as $namespace) {
            if (!$this->isTestsNamespace($namespace)) {
                continue;
            }

            /** @var list<Node\Name> $names */
            $names = $finder->findInstanceOf($namespace->stmts, Node\Name::class);
            foreach ($names as $name) {
                $class = $name->toString();
                if ($this->isForbidden($class)) {
                    $errors[] = RuleErrorBuilder::message(sprintf('Testy tylko przez HTTP: nie używaj %s.', $class))
                        ->identifier('app.httpOnlyTests')
                        ->line($name->getStartLine())
                        ->build();
                }
            }
        }

        return $errors;
    }

    private function isTestsNamespace(Node\Stmt\Namespace_ $namespace): bool
    {
        $name = $namespace->name?->toString() ?? '';

        return self::TESTS_NAMESPACE === $name || str_starts_with($name, self::TESTS_NAMESPACE.'\\');
    }

    private function isForbidden(string $class): bool
    {
        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (str_starts_with($class, $prefix)) {
                return true;
            }
        }

        return in_array($class, self::FORBIDDEN_CLASSES, true);
    }
}

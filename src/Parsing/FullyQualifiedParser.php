<?php

namespace Whsv26\Mediator\Parsing;

use Fp\Collections\ArrayList;
use Fp\Functional\Option\Option;
use Fp\Streams\Stream;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

use function Fp\Collection\at;
use function Fp\Evidence\proveNonEmptyString;
use function Fp\Evidence\proveOf;

/**
 * @psalm-type UseAlias = lowercase-string
 * @psalm-type UseFullyQualified = string
 */
final class FullyQualifiedParser
{
    /**
     * @var array<UseAlias, UseFullyQualified>
     */
    private array $uses;
    private string $namespace;

    public function __construct(
        private Namespace_ $namespaceStmt
    ) {
        $this->namespace = $this->parseNamespace($this->namespaceStmt);
        $this->uses = $this->parseUses($this->namespaceStmt);
    }

    public function parse(string|Name|Identifier $name): string
    {
        return match (true) {
            $name instanceof Name => $this->parseName($name),
            $name instanceof Identifier => $this->parseIdentifier($name),
            default => $this->parseString($name)
        };
    }

    private function parseNamespace(Namespace_ $namespaceStmt): string
    {
        return Option::fromNullable($namespaceStmt->name)
            ->map(fn(Name $name) => implode('\\', $name->parts))
            ->getOrElse('');
    }

    /**
     * @param Namespace_ $namespaceStmt
     * @return array<UseAlias, UseFullyQualified>
     */
    private function parseUses(Namespace_ $namespaceStmt): array
    {
        /** @var array<UseAlias, UseFullyQualified> */
        return Stream::emits($namespaceStmt->stmts)
            ->filterOf(Use_::class)
            ->map(fn(Use_ $use) => $this->parseUse($use))
            ->reduce(fn(array $acc, $cur) => array_merge($acc, $cur))
            ->getOrElse([]);
    }

    /**
     * @param Use_ $stmt
     * @return array<UseAlias, UseFullyQualified>
     */
    private function parseUse(Use_ $stmt): array
    {
        return Stream::emits($stmt->uses)
            ->filter(function (UseUse $use) use ($stmt) {
                $useType = ($use->type !== Use_::TYPE_UNKNOWN ? $use->type : $stmt->type);
                return Use_::TYPE_NORMAL === $useType;
            })
            ->toAssocArray(function (UseUse $use) {
                $usePath = implode('\\', $use->name->parts);
                $useAlias = $use->alias ? $use->alias->name : $use->name->getLast();
                return [strtolower($useAlias), $usePath];
            });
    }

    private function parseIdentifier(Identifier $id): string
    {
        return $this->namespace
            ? $this->namespace . '\\' . $id->toString()
            : $id->toString();
    }

    private function parseName(Name $name): string
    {
        return proveNonEmptyString($name->getAttribute('resolvedName'))
            ->orElse(fn() => proveOf($name, FullyQualified::class)
                ->map(fn(FullyQualified $fq) => implode('\\', $fq->parts))
            )
            ->getOrCall(fn() => $this->parseString(implode('\\', $name->parts)));
    }

    private function parseString(string $class): string
    {
        return $this->whenFullyQualified($class)
            ->orElse(fn() => $this->whenPartiallyQualified($class))
            ->orElse(fn() => $this->whenUseAlias($class))
            ->orElse(fn() => $this->whenNonGlobalNamespace($class))
            ->getOrElse($class);
    }

    /**
     * @return Option<string>
     */
    private function whenFullyQualified(string $class): Option
    {
        return Option::condLazy(
            str_starts_with($class, '\\'),
            fn() => substr($class, 1)
        );
    }

    /**
     * @return Option<string>
     */
    private function whenPartiallyQualified(string $class): Option
    {
        $classParts = ArrayList::collect(explode('\\', $class));

        return $classParts
            ->head()
            ->map(fn($namespace) => strtolower($namespace))
            ->filter(fn($alias) => isset($this->uses[$alias]))
            ->map(fn($alias) => $classParts
                ->drop(1)
                ->map(fn($part) => '\\' . $part)
                ->mkString(start: $this->uses[$alias], sep: '')
            );
    }

    /**
     * @return Option<string>
     */
    private function whenUseAlias(string $class): Option
    {
        return at($this->uses, strtolower($class));
    }

    /**
     * @return Option<string>
     */
    private function whenNonGlobalNamespace(string $class): Option
    {
        return proveNonEmptyString($this->namespace)
            ->map(fn($ns) => $ns . '\\' . $class);
    }
}

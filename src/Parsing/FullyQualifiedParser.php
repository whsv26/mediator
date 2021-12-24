<?php

namespace Whsv26\Mediator\Parsing;

use Fp\Functional\Option\Option;
use Fp\Streams\Stream;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

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
            $name instanceof Name => $this->fromName($name),
            $name instanceof Identifier => $this->fromIdentifier($name),
            default => $this->fromString($name)
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
            ->toHashMap(function(UseUse $use) {
                $usePath = implode('\\', $use->name->parts);
                $useAlias = $use->alias ? $use->alias->name : $use->name->getLast();

                return [strtolower($useAlias), $usePath];
            })
            ->toAssocArray()
            ->get();
    }

    private function fromIdentifier(Identifier $id): string
    {
        return $this->namespace
            ? $this->namespace . '\\' . $id->toString()
            : $id->toString();
    }

    private function fromName(Name $className): string {

        /** @var string|null */
        $resolved_name = $className->getAttribute('resolvedName');

        if ($resolved_name) {
            return $resolved_name;
        }

        if ($className instanceof Name\FullyQualified) {
            return implode('\\', $className->parts);
        }

        return $this->fromString(implode('\\', $className->parts));
    }

    private function fromString(string $class): string
    {
        $namespace = $this->namespace;
        $uses = $this->uses;

        if (($class[0] ?? '') === '\\') {
            return substr($class, 1);
        }

        if (str_contains($class, '\\')) {
            $classParts = explode('\\', $class);
            $firstNamespace = array_shift($classParts);

            if (isset($uses[strtolower($firstNamespace)])) {
                return $uses[strtolower($firstNamespace)] . '\\' . implode('\\', $classParts);
            }
        } elseif (isset($uses[strtolower($class)])) {
            return $uses[strtolower($class)];
        }

        return ($namespace ? $namespace . '\\' : '') . $class;
    }
}

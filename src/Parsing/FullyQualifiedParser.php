<?php

namespace Whsv26\Mediator\Parsing;

use PhpParser\Node\Name;

class FullyQualifiedParser
{
    /**
     * @param Name $className
     * @param string $namespace
     * @param array<lowercase-string, string> $uses
     * @return string
     */
    public static function fromName(
        Name $className,
        string $namespace,
        array $uses,
    ): string {

        /** @var string|null */
        $resolved_name = $className->getAttribute('resolvedName');

        if ($resolved_name) {
            return $resolved_name;
        }

        if ($className instanceof Name\FullyQualified) {
            return implode('\\', $className->parts);
        }

        return self::fromString(
            implode('\\', $className->parts),
            $namespace,
            $uses
        );
    }

    /**
     * @param string $class
     * @param string $namespace
     * @param array<lowercase-string, string> $uses
     * @return string
     */
    public static function fromString(
        string $class,
        string $namespace,
        array $uses,
    ): string {
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
<?php

namespace Whsv26\Mediator\Parsing;

use Fp\Functional\Option\Option;
use Fp\Functional\Unit;
use Fp\Streams\Stream;
use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;

use function Fp\classOf;
use function Fp\Collection\firstOf;
use function Fp\Collection\head;
use function Fp\Evidence\proveString;
use function Fp\Json\regExpMatch;
use function Fp\unit;

/**
 * @psalm-type Request = string
 * @psalm-type RequestHandler = string
 * @psalm-type UseAlias = lowercase-string
 * @psalm-type UseFullyQualified = string
 */
class RoutingMapParser
{
    private const REGEXP_REQUEST_TYPE = '/(?:@implements|@psalm-implements).*<.*,\s*(.*?)\s*>/';

    /**
     * @return array<Request, RequestHandler>
     */
    public function parseDirRecursive(string $dir): array
    {
        $directories = new RecursiveDirectoryIterator($dir);
        $files = new RecursiveIteratorIterator($directories);

        return Stream::emits($files)
            ->filterOf(SplFileInfo::class)
            ->filter(fn(SplFileInfo $info) => 'php' === $info->getExtension())
            ->filterMap(fn(SplFileInfo $info) => proveString($info->getRealPath()))
            ->filterMap(fn(string $path) => $this->parseFile($path))
            ->toHashMap(fn(array $pair) => $pair)
            ->toAssocArray()
            ->get();
    }

    /**
     * @return Option<array{Request, RequestHandler}>
     */
    private function parseFile(string $path): Option
    {
        return Option::do(function () use ($path) {
            $ast = (new ParserFactory())
                ->create(ParserFactory::PREFER_PHP7)
                ->parse(file_get_contents($path));

            $stmts = yield Option::fromNullable($ast);
            $namespaceStmt = yield firstOf($stmts, Namespace_::class);
            $classStmt = yield firstOf($namespaceStmt->stmts, Class_::class);

            $namespace = $this->parseNamespace($namespaceStmt);
            $uses = $this->parseUses($namespaceStmt);

            yield $this->proveHandlerClass($classStmt, $namespace, $uses);

            $requestHandlerClass = yield Option::some($classStmt)
                ->flatMap(fn(Class_ $class) => Option::fromNullable($class->name))
                ->map(fn(Identifier $name) => $namespace . '\\' . $name);

            $requestClass = yield Option::some($classStmt)
                ->flatMap(fn(Class_ $class) => Option::fromNullable($class->getDocComment()))
                ->map(fn(Doc $doc) => $doc->getText())
                ->flatMap(fn(string $doc) => regExpMatch(self::REGEXP_REQUEST_TYPE, $doc, 1))
                ->map(fn(string $cg) => FullyQualifiedParser::fromString($cg, $namespace, $uses));

            return [$requestClass, $requestHandlerClass];
        });
    }

    private function parseNamespace(Namespace_ $namespaceStmt): string
    {
        return Option::fromNullable($namespaceStmt->name)
            ->map(fn(Name $name) => implode('\\', $name->parts))
            ->getOrElse('');
    }

    /**
     * @return Option<Unit>
     */
    private function proveHandlerClass(Class_ $classStmt, string $namespace, array $uses): Option
    {
        return Option::some($classStmt)
            ->map(fn(Class_ $class) => $class->implements)
            ->filter(fn(array $implements) => 1 === count($implements))
            ->flatMap(fn(array $implements) => head($implements))
            ->map(fn(Name $name) => FullyQualifiedParser::fromName($name, $namespace, $uses))
            ->filter(function (string $name) {
                return classOf($name, QueryHandlerInterface::class)
                    || classOf($name, CommandHandlerInterface::class);
            })
            ->map(fn() => unit());
    }

    /**
     * @param Namespace_ $namespaceStmt
     * @return array<UseAlias, UseFullyQualified>
     */
    private function parseUses(Namespace_ $namespaceStmt): array
    {
        /** @var array<lowercase-string, string> */
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
}
<?php

namespace Whsv26\Mediator\Parsing;

use Fp\Functional\Option\Option;
use Fp\Functional\Unit;
use PhpParser\Comment\Doc;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;

use function Fp\classOf;
use function Fp\Collection\firstOf;
use function Fp\Collection\head;
use function Fp\Evidence\proveClassString;
use function Fp\Evidence\proveString;
use function Fp\Json\regExpMatch;
use function Fp\unit;

/**
 * @psalm-type Request = class-string
 * @psalm-type RequestHandler = class-string
 * @psalm-type UseAlias = lowercase-string
 * @psalm-type UseFullyQualified = string
 */
final class HandlerMapParser
{
    private const REGEXP_REQUEST_TYPE = '/(?:@implements|@psalm-implements).*<.*,\s*(.*?)\s*>/';

    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7);
    }

    /**
     * @return Option<array{Request, RequestHandler}>
     */
    public function parseFile(string $path): Option
    {
        return Option::do(function () use ($path) {
            $namespace = yield proveString(file_get_contents($path))
                ->map(fn(string $contents) => $this->parser->parse($contents))
                ->filter(fn(?array $stmts) => $stmts !== null)
                ->flatMap(fn(array $stmts) => firstOf($stmts, Namespace_::class));

            $fullyQualifiedParser = new FullyQualifiedParser($namespace);

            // Parse first class declaration
            // in the file
            $class = yield firstOf($namespace->stmts, Class_::class);

            // Assert that class implements
            // Query or Command handler interface
            yield $this->proveRequestHandlerClass($class, $fullyQualifiedParser);

            // 1. Parse request handler fqcn
            // 2. Parse request fqcn
            // 3. Build Map entry which is array{TKey, TValue}
            return yield $this->parseEntry($class, $fullyQualifiedParser);
        });
    }

    /**
     * @return Option<array{Request, RequestHandler}>
     */
    private function parseEntry(Class_ $classStmt, FullyQualifiedParser $fqp): Option
    {
        return Option::do(function () use ($classStmt, $fqp) {
            return [
                yield $this->parseRequestClass($classStmt, $fqp),
                yield $this->parseRequestHandlerClass($classStmt, $fqp)
            ];
        });
    }

    /**
     * @return Option<class-string>
     */
    private function parseRequestHandlerClass(Class_ $classStmt, FullyQualifiedParser $fqp): Option
    {
        return Option::some($classStmt)
            ->flatMap(fn(Class_ $class) => Option::fromNullable($class->name))
            ->map(fn(Identifier $name) => $fqp->parse($name))
            ->flatMap(fn(string $fqcn) => proveClassString($fqcn));
    }

    /**
     * @return Option<class-string>
     */
    private function parseRequestClass(Class_ $classStmt, FullyQualifiedParser $fqp): Option
    {
        return Option::some($classStmt)
            ->flatMap(fn(Class_ $class) => Option::fromNullable($class->getDocComment()))
            ->map(fn(Doc $doc) => $doc->getText())
            ->flatMap(fn(string $doc) => regExpMatch(self::REGEXP_REQUEST_TYPE, $doc, 1))
            ->map(fn(string $capturingGroup) => $fqp->parse($capturingGroup))
            ->flatMap(fn(string $fqcn) => proveClassString($fqcn));
    }

    /**
     * Prove that class implements
     * query or command handler interface
     *
     * @return Option<Unit>
     */
    private function proveRequestHandlerClass(Class_ $classStmt, FullyQualifiedParser $fqp): Option
    {
        return Option::some($classStmt)
            ->map(fn(Class_ $class) => $class->implements)
            ->filter(fn(array $implements) => 1 === count($implements))
            ->flatMap(fn(array $implements) => head($implements))
            ->map(fn(Name $name) => $fqp->parse($name))
            ->filter(function (string $name) {
                return classOf($name, QueryHandlerInterface::class)
                    || classOf($name, CommandHandlerInterface::class);
            })
            ->map(fn() => unit());
    }
}

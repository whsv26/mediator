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
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
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
        $directory = new RecursiveDirectoryIterator($dir);
        $fileFilter = new PhpFileFilterIterator($directory);
        $files = new RecursiveIteratorIterator($fileFilter);

        return Stream::emits($files)
            ->filterOf(SplFileInfo::class)
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
                ->parse(file_get_contents($path)) ?? [];

            $namespace = yield firstOf($ast, Namespace_::class);
            $class = yield firstOf($namespace->stmts, Class_::class);

            $fullyQualifiedParser = new FullyQualifiedParser($namespace);

            /**
             * Prove that class implements
             * Query or Command handler interface
             */
            yield $this->proveRequestHandlerClass($class, $fullyQualifiedParser);

            return [
                yield $this->parseRequestClass($class, $fullyQualifiedParser),
                yield $this->parseRequestHandlerClass($class, $fullyQualifiedParser)
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

<?php

namespace Whsv26\Mediator\Parsing;

use Fp\Collections\ArrayList;
use Fp\Collections\HashMap;
use Fp\Collections\Map;
use Fp\Functional\Option\Option;
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
use RegexIterator;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;
use function Fp\classOf;
use function Fp\Collection\filterOf;
use function Fp\Collection\firstOf;
use function Fp\Collection\head;
use function Fp\Json\regExpMatch;

/**
 * @psalm-type TQuery = string
 * @psalm-type TCommand = string
 * @psalm-type THandled = TQuery|TCommand
 * @psalm-type TQueryHandler = string
 * @psalm-type TCommandHandler = string
 * @psalm-type THandler = TQueryHandler|TCommandHandler
 */
class RoutingMapParser
{
    /**
     * @return array<THandled, THandler>
     */
    public function parseDirRecursive(string $dir): array
    {
        $directories = new RecursiveDirectoryIterator($dir);
        $files = new RecursiveIteratorIterator($directories);
        $phpFiles = new RegexIterator($files, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        return Stream::emits($phpFiles)
            ->filter(fn($files) => is_iterable($files))
            ->flatMap(fn(array $files) => $files)
            ->filterMap(fn(string $path) => $this->parseFile($path))
            ->toHashMap(fn(array $pair) => $pair)
            ->toAssocArray()
            ->get();
    }

    /**
     * @return Option<array{THandled, THandler}>
     */
    private function parseFile(string $path): Option
    {
        return Option::do(function () use ($path) {
            $ast = (new ParserFactory())
                ->create(ParserFactory::PREFER_PHP7)
                ->parse(file_get_contents($path));

            $tagRegExp = '/(?:@implements|@psalm-implements).*<.*,\s*(.*?)\s*>/';

            $namespaceStmt = Option::fromNullable($ast)
                ->flatMap(fn(array $stmts) => firstOf($stmts, Namespace_::class));

            $uses = $namespaceStmt
                ->toArrayList(fn(Namespace_ $stmt) => new ArrayList($stmt->stmts))
                ->filterOf(Use_::class)
                ->map(fn(Use_ $use) => $this->parseUse($use))
                ->reduce(fn($acc, $cur) => array_merge($acc, $cur))
                ->getOrElse([]);

            $namespace = $namespaceStmt
                ->flatMap(fn(Namespace_ $stmt) => Option::fromNullable($stmt->name))
                ->map(fn(Name $name) => implode('\\', $name->parts))
                ->getOrElse('');

            $classStmt = yield $namespaceStmt
                ->flatMap(fn(Namespace_ $stmt) => firstOf($stmt->stmts, Class_::class));

            yield Option::some($classStmt)
                ->map(fn(Class_ $class) => $class->implements)
                ->filter(fn(array $implements) => 1 === count($implements))
                ->flatMap(fn(array $implements) => head($implements))
                ->map(fn(Name $name) => FullyQualifiedParser::fromName($name, $namespace, $uses))
                ->filter(function (string $name) {
                    return classOf($name, QueryHandlerInterface::class)
                        || classOf($name, CommandHandlerInterface::class);
                });

            $handlerClass = yield Option::some($classStmt)
                ->flatMap(fn(Class_ $class) => Option::fromNullable($class->name))
                ->map(fn(Identifier $name) => $namespace . '\\' . $name);

            $handledClass = yield Option::some($classStmt)
                ->flatMap(fn(Class_ $class) => Option::fromNullable($class->getDocComment()))
                ->map(fn(Doc $doc) => $doc->getText())
                ->flatMap(fn(string $doc) => regExpMatch($tagRegExp, $doc, 1))
                ->map(fn(string $cg) => FullyQualifiedParser::fromString($cg, $namespace, $uses));

            return [$handledClass, $handlerClass];
        });
    }


    /**
     * @param Use_ $stmt
     * @return array<lowercase-string, string>
     */
    public function parseUse(Use_ $stmt): array
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
<?php

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Collections\Map;
use Fp\Functional\Option\Option;
use Fp\Streams\Stream;
use PhpParser\Comment\Doc;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\ParserFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;

use function Fp\classOf;
use function Fp\Collection\firstOf;
use function Fp\Collection\head;
use function Fp\Json\regExpMatch;

/**
 * @psalm-type TQuery = class-string
 * @psalm-type TCommand = class-string
 * @psalm-type THandled = TQuery|TCommand
 * @psalm-type TQueryHandler = class-string
 * @psalm-type TCommandHandler = class-string
 * @psalm-type THandler = TQueryHandler|TCommandHandler
 */
class RoutingMapParser
{
    /**
     * @return Map<THandled, THandler>
     */
    public function parseDirRecursive(string $dir): Map
    {
        $directories = new RecursiveDirectoryIterator($dir);
        $files = new RecursiveIteratorIterator($directories);
        $phpFiles = new RegexIterator($files, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        return Stream::emits($phpFiles)
            ->filterMap(fn(string $path) => $this->parseFile($dir))
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
            $classStmt = yield firstOf($ast, Class_::class);

            yield Option::some($classStmt)
                ->map(fn(Class_ $class) => $class->implements)
                ->filter(fn(array $implements) => 1 === count($implements))
                ->flatMap(fn(array $implements) => head($implements))
                ->map(fn(Name $name) => $name->toString())
                ->filter(function (string $name) {
                    return classOf($name, QueryHandlerInterface::class)
                        || classOf($name, CommandHandlerInterface::class);
                });

            $handlerClass = yield Option::some($classStmt)
                ->map(fn(Class_ $class) => $class->namespacedName)
                ->map(fn(Name $name) => $name->toString());

            $handledClass = Option::some($classStmt)
                ->flatMap(fn(Class_ $class) => Option::fromNullable($class->getDocComment()))
                ->map(fn(Doc $doc) => $doc->getReformattedText())
                ->flatMap(fn(string $doc) => regExpMatch($doc, $tagRegExp, 1));

            return [$handledClass, $handlerClass];
        });
    }
}
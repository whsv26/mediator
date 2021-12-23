<?php

declare(strict_types=1);

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Streams\Stream;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\CommandMiddlewareInterface;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;
use Whsv26\Mediator\Contract\QueryMiddlewareInterface;
use Whsv26\Mediator\Parsing\HandlerMapParser;

use function Fp\Evidence\proveClassString;
use function Fp\Evidence\proveString;
use function Fp\Reflection\getReflectionClass;

class MediatorCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $handlerMapParser = new HandlerMapParser();

        $findServices = fn(string $tag): array => $this->findAndSortTaggedServices($tag, $container);

        $commandMiddlewares = $findServices(CommandMiddlewareInterface::TAG);
        $queryMiddlewares = $findServices(QueryMiddlewareInterface::TAG);
        $commandHandlers = $findServices(CommandHandlerInterface::TAG);
        $queryHandlers = $findServices(QueryHandlerInterface::TAG);

        $handlerMap = Stream::emits($commandHandlers)
            ->appendedAll($queryHandlers)
            ->filterMap(fn(Reference $ref) => proveClassString((string) $ref))
            ->filterMap(fn(string $id) => getReflectionClass($id)->toOption())
            ->filterMap(fn(ReflectionClass $class) => proveString($class->getFileName()))
            ->filterMap(fn(string $file) => $handlerMapParser->parseFile($file))
            ->map(fn(array $pair) => [$pair[0], new Reference($pair[1])])
            ->toAssocArray(fn(array $pair) => $pair);

        $container
            ->getDefinition(MediatorInterface::class)
            ->setArguments([
                new ServiceLocatorArgument($handlerMap),
                new IteratorArgument($commandMiddlewares),
                new IteratorArgument($queryMiddlewares),
            ]);
    }
}

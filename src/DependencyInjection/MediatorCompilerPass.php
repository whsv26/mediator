<?php

declare(strict_types=1);

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Parsing\HandlerMapParser;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

class MediatorCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $handlerMapParser = new HandlerMapParser();
        $projectDir = $container->getParameter('kernel.project_dir');

        assert(is_string($projectDir));

        $handlerMap = $handlerMapParser
            ->parseDirRecursive($projectDir)
            ->map(fn(array $pair) => [$pair[0], new Reference($pair[1])])
            ->toAssocArray(fn(array $pair) => $pair);

        $commandMiddlewares = $this->findAndSortTaggedServices(
            'mediator.command_middleware',
            $container
        );

        $queryMiddlewares = $this->findAndSortTaggedServices(
            'mediator.query_middleware',
            $container
        );

        $container
            ->getDefinition(MediatorInterface::class)
            ->setArguments([
                service_locator($handlerMap),
                new IteratorArgument($commandMiddlewares),
                new IteratorArgument($queryMiddlewares),
            ]);
    }
}

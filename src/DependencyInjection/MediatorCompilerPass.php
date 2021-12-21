<?php

declare(strict_types=1);

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Parsing\HandlerMapParser;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

class MediatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $handlerMapParser = new HandlerMapParser();
        $projectDir = $container->getParameter('kernel.project_dir');

        assert(is_string($projectDir));

        $handlerMap = $handlerMapParser
            ->parseDirRecursive($projectDir)
            ->map(fn(array $pair) => [$pair[0], new Reference($pair[1])])
            ->toAssocArray(fn(array $pair) => $pair);

        $container
            ->getDefinition(MediatorInterface::class)
            ->setArguments([service_locator($handlerMap)]);
    }
}

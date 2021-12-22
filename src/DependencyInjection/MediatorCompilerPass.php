<?php

declare(strict_types=1);

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Collections\ArrayList;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Parsing\HandlerMapParser;
use Whsv26\Tests\Dummy\DummyMiddlewareOne;
use Whsv26\Tests\Dummy\DummyMiddlewareTwo;

use function Symfony\Component\DependencyInjection\Loader\Configurator\iterator;
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

        $middlewares = ArrayList::collect([DummyMiddlewareOne::class, DummyMiddlewareTwo::class])
            ->map(fn($fqcn) => new Reference($fqcn))
            ->toArray();

        $container
            ->getDefinition(MediatorInterface::class)
            ->setArguments([
                service_locator($handlerMap),
                iterator($middlewares),
                iterator($middlewares),
            ]);
    }
}

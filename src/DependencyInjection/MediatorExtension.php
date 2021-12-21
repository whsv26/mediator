<?php

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Parsing\RoutingMapParser;

/**
 * @psalm-type TQuery = class-string
 * @psalm-type TCommand = class-string
 * @psalm-type THandled = TQuery|TCommand
 * @psalm-type TQueryHandler = class-string
 * @psalm-type TCommandHandler = class-string
 * @psalm-type THandler = TQueryHandler|TCommandHandler
 */
class MediatorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configDir = new FileLocator(__DIR__ . '/../../config');

        // Load the bundle's service declarations
        $loader = new PhpFileLoader($container, $configDir);

        $loader->load('services.php');

        if ('test' === $_ENV['APP_ENV']) {
            $loader->load('services_test.php');
        }

        // Apply our config schema to the given app's configs
        // $schema = new ConfigSchema();
        // $options = $this->processConfiguration($schema, $configs);

        $routingMapParser = new RoutingMapParser();
        $projectDir = $container->getParameter('kernel.project_dir');

        assert(is_string($projectDir));

        $routingMap = $routingMapParser->parseDirRecursive($projectDir);

        $container
            ->getDefinition(MediatorInterface::class)
            ->replaceArgument(1, $routingMap);
    }
}

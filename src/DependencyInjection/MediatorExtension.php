<?php

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;
use Whsv26\Mediator\Contract\RequestInterface;
use Whsv26\Mediator\Parsing\HandlerMapParser;

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

        $container
            ->registerForAutoconfiguration(CommandHandlerInterface::class)
            ->addTag('mediator.command_handler');

        $container
            ->registerForAutoconfiguration(QueryHandlerInterface::class)
            ->addTag('mediator.query_handler');

        // Apply our config schema to the given app's configs
        // $schema = new ConfigSchema();
        // $options = $this->processConfiguration($schema, $configs);
    }
}

<?php

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\CommandMiddlewareInterface;
use Whsv26\Mediator\Contract\MediatorInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;
use Whsv26\Mediator\Contract\QueryMiddlewareInterface;
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

        $this->registerForAutoconfiguration($container);

//        $options = $this->mergeConfigurations($configs);
    }

    /**
     * @psalm-type MediatorConfig = array{
     *     middlewares: list<array{
     *         attribute: class-string,
     *         middleware: string
     *     }>
     * }
     * @return MediatorConfig
     */
    private function mergeConfigurations(array $configs): array
    {
        /** @var MediatorConfig */
        return $this->processConfiguration(new ConfigSchema(), $configs);
    }

    private function registerForAutoconfiguration(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(CommandHandlerInterface::class)
            ->addTag(CommandHandlerInterface::TAG);

        $container
            ->registerForAutoconfiguration(QueryHandlerInterface::class)
            ->addTag(QueryHandlerInterface::TAG);

        $container
            ->registerForAutoconfiguration(CommandMiddlewareInterface::class)
            ->addTag(CommandMiddlewareInterface::TAG);

        $container
            ->registerForAutoconfiguration(QueryMiddlewareInterface::class)
            ->addTag(QueryMiddlewareInterface::TAG);
    }
}

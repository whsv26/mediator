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

        $this->addTags($container);

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

    private function addTags(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(CommandHandlerInterface::class)
            ->addTag('mediator.command_handler');

        $container
            ->registerForAutoconfiguration(QueryHandlerInterface::class)
            ->addTag('mediator.query_handler');

        $container
            ->registerForAutoconfiguration(CommandMiddlewareInterface::class)
            ->addTag('mediator.command_middleware');

        $container
            ->registerForAutoconfiguration(QueryMiddlewareInterface::class)
            ->addTag('mediator.query_middleware');
    }
}

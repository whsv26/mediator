<?php

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Functional\Option\Option;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Whsv26\Mediator\Contract\CommandHandlerInterface;
use Whsv26\Mediator\Contract\CommandMiddlewareInterface;
use Whsv26\Mediator\Contract\QueryHandlerInterface;
use Whsv26\Mediator\Contract\QueryMiddlewareInterface;

/**
 * @psalm-type TQuery = class-string
 * @psalm-type TCommand = class-string
 * @psalm-type THandled = TQuery|TCommand
 * @psalm-type TQueryHandler = class-string
 * @psalm-type TCommandHandler = class-string
 * @psalm-type THandler = TQueryHandler|TCommandHandler
 * @psalm-type MediatorConfig = array{
 *     query: array{middlewares: list<class-string>},
 *     command: array{middlewares: list<class-string>}
 * }
 */
class MediatorExtension extends Extension
{
    /**
     * @param ?MediatorConfig $configs
     */
    public function __construct(
        private ?array $configs = null
    ) { }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

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

        /**
         * @var MediatorConfig $processedConfigs
         */
        $processedConfigs = $this->processConfiguration(new Configuration(), $configs);

        $this->configs = $processedConfigs;
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

    /**
     * @return Option<MediatorConfig>
     */
    public function getMediatorConfigs(): Option
    {
        return Option::fromNullable($this->configs);
    }
}

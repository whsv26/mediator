<?php

namespace Whsv26\Mediator\DependencyInjection;

use Fp\Functional\Option\Option;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @psalm-type MediatorConfig = array{
 *     bus: array{
 *         query: string,
 *         command: string,
 *         event: string
 *     }
 * }
 */
final class MediatorExtension extends Extension
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

    /**
     * @return Option<MediatorConfig>
     */
    public function getProcessedConfiguration(): Option
    {
        return Option::fromNullable($this->configs);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->loadServices($container);

        /**
         * @var MediatorConfig $processedConfigs
         */
        $processedConfigs = $this->processConfiguration(new Configuration(), $configs);

        $this->configs = $processedConfigs;
    }

    /**
     * Load the bundle's service declarations
     */
    private function loadServices(ContainerBuilder $container): void
    {
        $configDir = new FileLocator(__DIR__ . '/../../config');
        $loader = new PhpFileLoader($container, $configDir);

        $loader->load('services.php');

        if ('test' === ($_ENV['APP_ENV'] ?? getenv('APP_ENV'))) {
            $loader->load('services_test.php');
        }
    }
}

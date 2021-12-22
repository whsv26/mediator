<?php

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigSchema implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mediator');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('middlewares')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('attribute')->end()
                            ->scalarNode('middleware')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

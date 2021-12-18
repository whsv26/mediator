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
            ->fixXmlConfig('middleware')
            ->children()
                ->arrayNode('query')
                ->cannotBeEmpty()
                ->end()
            ->children()
                ->arrayNode('command')
                ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
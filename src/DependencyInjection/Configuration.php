<?php

namespace Whsv26\Mediator\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress MixedMethodCall,PossiblyNullReference,PossiblyUndefinedMethod
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mediator');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('bus')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('query')->defaultValue('query.bus')->end()
                        ->scalarNode('command')->defaultValue('command.bus')->end()
                        ->scalarNode('event')->defaultValue('event.bus')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

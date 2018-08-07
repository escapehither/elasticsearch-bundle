<?php

namespace EscapeHither\SearchManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('escape_hither_search_manager');
        $rootNode
            ->children()
                ->scalarNode('host')
                  ->defaultValue('localhost')
                ->end()
                ->scalarNode('port')
                  ->defaultValue('9200')
                ->end()
                ->arrayNode('indexes')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')
                            ->end()
                            ->scalarNode('port')
                            ->end()
                            ->scalarNode('entity')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('index_name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('tags')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('include')
                                            ->info('This values are the list of fields to include.')
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

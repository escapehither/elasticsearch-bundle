<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 21/01/17
 * Time: 22:06
 */

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
                            ->arrayNode('facets')
                                ->children()
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
                                    ->arrayNode('tags_relation')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('entity')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('index_name')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('field_name')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('display_name')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('tag_type')->isRequired()->cannotBeEmpty()->end()
                                                ->arrayNode('include')
                                                    ->info('This values are the list of fields to include.')
                                                    ->prototype('scalar')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('dates')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('field_name')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('display_name')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('tag_type')->isRequired()->cannotBeEmpty()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('ranges')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('field_name')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('display_name')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('tag_type')->isRequired()->cannotBeEmpty()->end()
                                            ->end()
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

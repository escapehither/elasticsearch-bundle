<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EscapeHitherSearchManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        // add a new parameter
        $container->setParameter('escape_hither.search_manager.host', $config['host']);
        $indexes = [];

        if (!empty($config['indexes'])) {
            foreach ($config['indexes'] as $key => $value) {
                //TODO CHECK IF EXIST.
                $indexes[$value['entity']] = $value;

                if (!empty($value['facets']['tags_relation'])) {
                    foreach ($value['facets']['tags_relation'] as $keyTag => $tag) {
                        $indexes[$tag['entity']] = $tag;
                    }
                }
            }
        }

        $container->setParameter('escape_hither.search_manager.indexes', $indexes);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}

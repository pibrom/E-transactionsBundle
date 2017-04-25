<?php

namespace Snowbaha\EtransactionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('snowbaha_etransactions');

        $rootNode
            ->children()
            ->scalarNode('debug')->defaultValue('ON')->end()
            ->scalarNode('site')->defaultValue('')->end()
            ->scalarNode('key_dev')->defaultValue('')->end()
            ->scalarNode('key_prod')->defaultValue('')->end()
            ->scalarNode('retour')->defaultValue('')->end()
            ->scalarNode('env_mode')->defaultValue('TEST')->end()
            ->scalarNode('hash')->defaultValue('SHA512')->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}

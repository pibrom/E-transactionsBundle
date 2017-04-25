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
            ->scalarNode('site_id')->defaultValue('')->end()
            ->scalarNode('key_dev')->defaultValue('')->end()
            ->scalarNode('key_prod')->defaultValue('')->end()
            ->scalarNode('url_return')->defaultValue('')->end()
            ->scalarNode('return_mode')->defaultValue('')->end()
            ->scalarNode('env_mode')->defaultValue('TEST')->end()
            ->scalarNode('page_action')->defaultValue('PAYMENT')->end()
            ->scalarNode('action_mode')->defaultValue('INTERACTIVE')->end()
            ->scalarNode('payment_config')->defaultValue('SINGLE')->end()
            ->scalarNode('hash')->defaultValue('SHA512')->end()  //*
            ->scalarNode('redirect_success_timeout')->defaultValue('1')->end()
            ->scalarNode('redirect_success_message')->defaultValue('Redirection vers la boutique dans quelques instants')->end()
            ->scalarNode('redirect_error_timeout')->defaultValue('1')->end()
            ->scalarNode('redirect_error_message')->defaultValue('Redirection vers la boutique dans quelques instants')->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}

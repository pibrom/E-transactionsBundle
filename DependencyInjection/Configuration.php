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
            ->scalarNode('identifiant')->defaultValue('')->end() // Identifiant interne (fourni par l’assistance)
            ->scalarNode('site')->defaultValue('')->end()        // Numéro de site (fourni par l’assistance E-transactions)
            ->scalarNode('rang')->defaultValue('')->end()       // Numéro de rang (fourni par l’assistance)
            ->scalarNode('key_dev')->defaultValue('')->end()    // Certificat TEST
            ->scalarNode('key_prod')->defaultValue('')->end()   // Certificat PROD
            ->scalarNode('retour')->defaultValue('')->end()     // Liste des variables à retourner par E-transactions
            ->scalarNode('env_mode')->defaultValue('TEST')->end() // Type Environnement
            ->scalarNode('hash')->defaultValue('SHA512')->end()     // Type d’algorithme de hachage pour le calcul de l’empreinte
            ->scalarNode('check_signature')->defaultValue(false)->end()     // if you want to check the signature
            ->end()
        ;
        // + PBX_TOTAL = Montant total de la transaction
        // + PBX_PORTEUR = Adresse E-mail de l’acheteur

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}

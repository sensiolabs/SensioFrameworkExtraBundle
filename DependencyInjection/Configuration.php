<?php

namespace Sensio\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * FrameworkExtraBundle configuration structure.
 *
 * @author Henrik Bjornskov <hb@peytz.dk>
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return Symfony\Component\Config\Definition\NodeInterface
     */
    public function getConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sensio_framework_extra', 'array');

        $rootNode
            ->children()
                ->arrayNode('router')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('annotations')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('request')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('converters')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('view')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('annotations')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('annotations')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
    }
}

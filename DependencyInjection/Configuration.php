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
                    ->children()
                        ->booleanNode('annotations')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('request')
                    ->children()
                        ->booleanNode('converters')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('view')
                    ->children()
                        ->booleanNode('annotations')->defaultValue(true)->end()
                        ->booleanNode('manager_null_arguments')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->children()
                        ->booleanNode('annotations')->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
    }
}

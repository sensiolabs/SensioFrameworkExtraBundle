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
            ->arrayNode('router')
                ->booleanNode('annotations')->defaultValue(true)->end()
            ->end()
            ->arrayNode('request')
                ->booleanNode('converters')->defaultValue(true)->end()
            ->end()
            ->arrayNode('view')
                ->booleanNode('annotations')->defaultValue(true)->end()
                ->booleanNode('manager_null_arguments')->defaultValue(true)->end()
            ->end()
            ->arrayNode('cache')
                ->booleanNode('annotations')->defaultValue(true)->end()
            ->end();

        return $treeBuilder->buildTree();
    }
}

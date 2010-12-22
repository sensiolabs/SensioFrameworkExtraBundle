<?php

namespace Bundle\Sensio\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * ExtraExtension.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ExtraExtension extends Extension
{
    /**
     * Loads the extra configuration.
     *
     * @param array $config  An array of configuration settings
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad($config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');

        $annotationsToLoad = array();

        if (!isset($config['router']['annotations']) || $config['router']['annotations']) {
            $annotationsToLoad[] = 'routing.xml';
        }

        if (!isset($config['request']['converters']) || $config['request']['converters']) {
            $annotationsToLoad[] = 'converters.xml';
        }

        if (!isset($config['view']['annotations']) || $config['view']['annotations']) {
            $annotationsToLoad[] = 'view.xml';
        }

        if (!isset($config['cache']['annotations']) || $config['cache']['annotations']) {
            $annotationsToLoad[] = 'cache.xml';
        }

        if ($annotationsToLoad) {
            // must be first
            $loader->load('annotations.xml');

            foreach ($annotationsToLoad as $config) {
                $loader->load($config);
            }
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/symfony_extra';
    }

    public function getAlias()
    {
        return 'extra';
    }
}

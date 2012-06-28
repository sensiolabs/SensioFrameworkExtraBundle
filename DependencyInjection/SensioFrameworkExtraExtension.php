<?php

namespace Sensio\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\FileLocator;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * SensioFrameworkExtraExtension.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SensioFrameworkExtraExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $annotationsToLoad = array();

        if ($config['router']['annotations']) {
            $annotationsToLoad[] = 'routing.xml';
        }

        if ($config['request']['converters']) {
            $annotationsToLoad[] = 'converters.xml';
        }

        if ($config['view']['annotations']) {
            $annotationsToLoad[] = 'view.xml';
        }

        if ($config['cache']['annotations']) {
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
        return 'http://symfony.com/schema/dic/symfony_extra';
    }
}

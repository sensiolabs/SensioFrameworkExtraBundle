<?php

namespace Sensio\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

            $this->addClassesToCompile(array(
                'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ControllerListener',
            ));
        }

        if ($config['request']['converters']) {
            $annotationsToLoad[] = 'converters.xml';

            $this->addClassesToCompile(array(
                // cannot be added because it has some annotations
                //'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ParamConverter',
                'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ParamConverterListener',
                'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DateTimeParamConverter',
                'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DoctrineParamConverter',
                'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\ParamConverterInterface',
                'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\ParamConverterManager',
            ));
        }

        if ($config['view']['annotations']) {
            $annotationsToLoad[] = 'view.xml';

            $this->addClassesToCompile(array(
                'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\TemplateListener',
            ));
        }

        if ($config['cache']['annotations']) {
            $annotationsToLoad[] = 'cache.xml';

            $this->addClassesToCompile(array(
                'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\HttpCacheListener',
            ));
        }

        if ($config['security']['annotations']) {
            $annotationsToLoad[] = 'security.xml';

            $this->addClassesToCompile(array(
                'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\SecurityListener',
            ));
        }

        if ($annotationsToLoad) {
            // must be first
            $loader->load('annotations.xml');

            foreach ($annotationsToLoad as $config) {
                $loader->load($config);
            }

            $this->addClassesToCompile(array(
                'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ConfigurationAnnotation',
            ));
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

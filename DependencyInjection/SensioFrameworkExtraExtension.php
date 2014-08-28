<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

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

            if (class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage') && class_exists('Symfony\Component\Security\Core\Authorization\ExpressionLanguage')) {
                $container->setAlias('sensio_framework_extra.security.expression_language', new Alias($config['security']['expression_language'], false));
            } else {
                $container->removeDefinition('sensio_framework_extra.security.expression_language.default');
            }

            $this->addClassesToCompile(array(
                'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\SecurityListener',
            ));
        }

        if ($annotationsToLoad) {
            // must be first
            $loader->load('annotations.xml');

            foreach ($annotationsToLoad as $configFile) {
                $loader->load($configFile);
            }

            $this->addClassesToCompile(array(
                'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ConfigurationAnnotation',
            ));

            if ($config['request']['converters']) {
                $container->getDefinition('sensio_framework_extra.converter.listener')->replaceArgument(1, $config['request']['auto_convert']);
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

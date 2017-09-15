<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\ClassExistenceResource;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage as SecurityExpressionLanguage;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SensioFrameworkExtraExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $annotationsToLoad = array();

        if ($config['router']['annotations']) {
            $annotationsToLoad[] = 'routing.xml';

            if (PHP_VERSION_ID < 70000) {
                $this->addClassesToCompile(array(
                    'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ControllerListener',
                ));
            }
        }

        if ($config['request']['converters']) {
            $annotationsToLoad[] = 'converters.xml';

            $container->setParameter('sensio_framework_extra.disabled_converters', is_string($config['request']['disable']) ? implode(',', $config['request']['disable']) : $config['request']['disable']);

            $container->addResource(new ClassExistenceResource(ExpressionLanguage::class));
            if (class_exists(ExpressionLanguage::class)) {
                $container->setAlias('sensio_framework_extra.converter.doctrine.orm.expression_language', new Alias('Symfony\Component\ExpressionLanguage\ExpressionLanguage', false));
            } else {
                $container->removeDefinition('Symfony\Component\ExpressionLanguage\ExpressionLanguage');
            }

            if (PHP_VERSION_ID < 70000) {
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
        }

        if ($config['view']['annotations']) {
            $annotationsToLoad[] = 'view.xml';

            if (PHP_VERSION_ID < 70000) {
                $this->addClassesToCompile(array(
                    'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\TemplateListener',
                ));
            }
        }

        if ($config['cache']['annotations']) {
            $annotationsToLoad[] = 'cache.xml';

            if (PHP_VERSION_ID < 70000) {
                $this->addClassesToCompile(array(
                    'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\HttpCacheListener',
                ));
            }
        }

        if ($config['security']['annotations']) {
            $annotationsToLoad[] = 'security.xml';

            $container->addResource(new ClassExistenceResource(ExpressionLanguage::class));
            if (class_exists(ExpressionLanguage::class)) {
                // this resource can only be added if ExpressionLanguage exists (to avoid a fatal error)
                $container->addResource(new ClassExistenceResource(SecurityExpressionLanguage::class));
                if (class_exists(SecurityExpressionLanguage::class)) {
                    $container->setAlias('sensio_framework_extra.security.expression_language', new Alias($config['security']['expression_language'], false));
                } else {
                    $container->removeDefinition('Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage');
                }
            } else {
                $container->removeDefinition('Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage');
            }

            if (PHP_VERSION_ID < 70000) {
                $this->addClassesToCompile(array(
                    'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\SecurityListener',
                ));
            }
        }

        if ($annotationsToLoad) {
            // must be first
            $loader->load('annotations.xml');

            foreach ($annotationsToLoad as $configFile) {
                $loader->load($configFile);
            }

            if (PHP_VERSION_ID < 70000) {
                $this->addClassesToCompile(array(
                    'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ConfigurationAnnotation',
                ));
            }

            if ($config['request']['converters']) {
                $container->getDefinition('Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener')->replaceArgument(1, $config['request']['auto_convert']);
            }
        }

        if (!empty($config['templating']['controller_patterns'])) {
            $container
                ->getDefinition('Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser')
                ->addArgument($config['templating']['controller_patterns']);
        }

        if ($config['psr_message']['enabled']) {
            $loader->load('psr7.xml');
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

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

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\ClassExistenceResource;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;
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

        $annotationsToLoad = [];
        $definitionsToRemove = [];

        if ($config['router']['annotations']) {
            @trigger_error(sprintf('Enabling the "sensio_framework_extra.router.annotations" configuration is deprecated since version 5.2. Set it to false and use the "%s" annotation from Symfony itself.', \Symfony\Component\Routing\Annotation\Route::class), \E_USER_DEPRECATED);

            if (Kernel::MAJOR_VERSION < 5) {
                $annotationsToLoad[] = 'routing-4.4.xml';
            } else {
                $annotationsToLoad[] = 'routing.xml';
            }
        }

        if ($config['request']['converters']) {
            $annotationsToLoad[] = 'converters.xml';

            $container->registerForAutoconfiguration(ParamConverterInterface::class)
                ->addTag('request.param_converter');

            $container->setParameter('sensio_framework_extra.disabled_converters', \is_string($config['request']['disable']) ? implode(',', $config['request']['disable']) : $config['request']['disable']);

            $container->addResource(new ClassExistenceResource(ExpressionLanguage::class));
            if (class_exists(ExpressionLanguage::class)) {
                $container->setAlias('sensio_framework_extra.converter.doctrine.orm.expression_language', new Alias('sensio_framework_extra.converter.doctrine.orm.expression_language.default', false));
            } else {
                $definitionsToRemove[] = 'sensio_framework_extra.converter.doctrine.orm.expression_language.default';
            }
        }

        if ($config['view']['annotations']) {
            $annotationsToLoad[] = 'view.xml';
        }

        if ($config['cache']['annotations']) {
            $annotationsToLoad[] = 'cache.xml';
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
                    $definitionsToRemove[] = 'sensio_framework_extra.security.expression_language.default';
                }
            } else {
                $definitionsToRemove[] = 'sensio_framework_extra.security.expression_language.default';
            }
        }

        if ($annotationsToLoad) {
            // must be first
            $loader->load('annotations.xml');

            foreach ($annotationsToLoad as $configFile) {
                $loader->load($configFile);
            }

            if ($config['request']['converters']) {
                $container->getDefinition('sensio_framework_extra.converter.listener')->replaceArgument(1, $config['request']['auto_convert']);
            }
        }

        if (!empty($config['templating']['controller_patterns'])) {
            $container
                ->getDefinition('sensio_framework_extra.view.guesser')
                ->addArgument($config['templating']['controller_patterns']);
        }

        foreach ($definitionsToRemove as $definition) {
            $container->removeDefinition($definition);
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

    /**
     * @return string
     */
    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/symfony_extra';
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\SensioFrameworkExtraExtension;
use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\LegacyPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SensioFrameworkExtraExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLegacySecurityListener()
    {
        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $this->markTestSkipped();
        }

        $this->iniSet('error_reporting', -1 & ~E_USER_DEPRECATED);

        $container = new ContainerBuilder();
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('security.xml');
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../vendor/symfony/security-bundle/Symfony/Bundle/SecurityBundle/Resources/config'));
        $loader->load('security.xml');
        $this->registerLegacyPass($container);
        $container->compile();

        $securityContext = $container->getDefinition('sensio_framework_extra.security.listener')->getArgument(0);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $securityContext);
    }

    public function testSecurityListener()
    {
        if (!interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $this->markTestSkipped();
        }

        $container = new ContainerBuilder();
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('security.xml');
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../vendor/symfony/security-bundle/Symfony/Bundle/SecurityBundle/Resources/config'));
        $loader->load('security.xml');
        $this->registerLegacyPass($container);
        $container->compile();

        $this->assertNull($container->getDefinition('sensio_framework_extra.security.listener')->getArgument(0));
    }

    public function testDefaultExpressionLanguageConfig()
    {
        $container = new ContainerBuilder();

        $extension = new SensioFrameworkExtraExtension();
        $extension->load(array(), $container);

        $this->assertAlias($container, 'sensio_framework_extra.security.expression_language.default', 'sensio_framework_extra.security.expression_language');
    }

    public function testOverrideExpressionLanguageConfig()
    {
        $container = new ContainerBuilder();

        $extension = new SensioFrameworkExtraExtension();
        $config = array(
            'security'  => array(
                'expression_language' => 'acme.security.expression_language',
            ),
        );

        $container->setDefinition('acme.security.expression_language', new Definition());

        $extension->load(array($config), $container);

        $this->assertAlias($container, 'acme.security.expression_language', 'sensio_framework_extra.security.expression_language');
    }

    private function assertAlias(ContainerBuilder $container, $value, $key)
    {
        $this->assertEquals($value, (string) $container->getAlias($key), sprintf('%s alias is correct', $key));
    }

    private function registerLegacyPass(ContainerBuilder $container)
    {
        $passConfig = $container->getCompiler()->getPassConfig();
        $passConfig->setAfterRemovingPasses(array());
        $passConfig->setBeforeOptimizationPasses(array());
        $passConfig->setBeforeRemovingPasses(array());
        $passConfig->setOptimizationPasses(array());
        $passConfig->setRemovingPasses(array());
        $container->addCompilerPass(new LegacyPass());
    }
}

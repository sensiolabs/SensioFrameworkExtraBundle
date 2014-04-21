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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SensioFrameworkExtraExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $configuration;

    public function setUp()
    {
        $this->configuration = new ContainerBuilder();
    }

    public function tearDown()
    {
        $this->configuration = null;
    }

    public function testDefaultExpressionLanguageConfig()
    {
        $loader = new SensioFrameworkExtraExtension();
        $loader->load(array(), $this->configuration);

        $this->assertAlias('sensio_framework_extra.security.expression_language.default', 'sensio_framework_extra.security.expression_language');
    }

    public function testOverrideExpressionLanguageConfig()
    {
        $loader = new SensioFrameworkExtraExtension();
        $config = array(
            'security'  => array(
                'expression_language' => 'acme.security.expression_language'
            )
        );

        $this->configuration->setDefinition('acme.security.expression_language', new Definition());

        $loader->load(array($config), $this->configuration);

        $this->assertAlias('acme.security.expression_language', 'sensio_framework_extra.security.expression_language');
    }

    /**
     * @param string $value
     * @param string $key
     */
    private function assertAlias($value, $key)
    {
        $this->assertEquals($value, (string) $this->configuration->getAlias($key), sprintf('%s alias is correct', $key));
    }
}

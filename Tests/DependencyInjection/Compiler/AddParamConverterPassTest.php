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

use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddParamConverterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddParamConverterPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddParamConverterPass
     */
    private $pass;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $managerDefinition;

    public function setUp()
    {
        $this->pass = new AddParamConverterPass();
        $this->container = new ContainerBuilder();
        $this->managerDefinition = new Definition();
        $this->container->setDefinition('sensio_framework_extra.converter.manager', $this->managerDefinition);
        $this->container->setParameter('sensio_framework_extra.disabled_converters', array());
    }

    public function testProcessNoOpNoManager()
    {
        $this->container->removeDefinition('sensio_framework_extra.converter.manager');
        $this->pass->process($this->container);
    }

    public function testProcessNoOpNoTaggedServices()
    {
        $this->pass->process($this->container);
        $this->assertCount(0, $this->managerDefinition->getMethodCalls());
    }

    public function testProcessAddsTaggedServices()
    {
        $paramConverter1 = new Definition();
        $paramConverter1->setTags(array(
            'request.param_converter' => array(
                array(
                    'priority' => 'false',
                ),
            ),
        ));

        $paramConverter2 = new Definition();
        $paramConverter2->setTags(array(
            'request.param_converter' => array(
                array(
                    'converter' => 'foo',
                ),
            ),
        ));

        $paramConverter3 = new Definition();
        $paramConverter3->setTags(array(
            'request.param_converter' => array(
                array(
                    'priority' => 5,
                ),
            ),
        ));

        $this->container->setDefinition('param_converter_one', $paramConverter1);
        $this->container->setDefinition('param_converter_two', $paramConverter2);
        $this->container->setDefinition('param_converter_three', $paramConverter3);

        $this->pass->process($this->container);

        $methodCalls = $this->managerDefinition->getMethodCalls();
        $this->assertCount(3, $methodCalls);
        $this->assertEquals(array('add', array(new Reference('param_converter_one'), 0, null)), $methodCalls[0]);
        $this->assertEquals(array('add', array(new Reference('param_converter_two'), 0, 'foo')), $methodCalls[1]);
        $this->assertEquals(array('add', array(new Reference('param_converter_three'), 5, null)), $methodCalls[2]);
    }

    public function testProcessExplicitAddsTaggedServices()
    {
        $paramConverter1 = new Definition();
        $paramConverter1->setTags(array(
            'request.param_converter' => array(
                array(
                    'priority' => 'false',
                    'converter' => 'bar',
                ),
            ),
        ));

        $paramConverter2 = new Definition();
        $paramConverter2->setTags(array(
            'request.param_converter' => array(
                array(
                    'converter' => 'foo',
                ),
            ),
        ));

        $paramConverter3 = new Definition();
        $paramConverter3->setTags(array(
            'request.param_converter' => array(
                array(
                    'priority' => 5,
                    'converter' => 'baz',
                ),
            ),
        ));

        $this->container->setDefinition('param_converter_one', $paramConverter1);
        $this->container->setDefinition('param_converter_two', $paramConverter2);
        $this->container->setDefinition('param_converter_three', $paramConverter3);

        $this->container->setParameter('sensio_framework_extra.disabled_converters', array('bar', 'baz'));

        $this->pass->process($this->container);

        $methodCalls = $this->managerDefinition->getMethodCalls();
        $this->assertCount(1, $methodCalls);
        $this->assertEquals(array('add', array(new Reference('param_converter_two'), 0, 'foo')), $methodCalls[0]);
    }
}

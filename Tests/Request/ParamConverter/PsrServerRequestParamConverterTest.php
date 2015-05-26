<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\PsrServerRequestParamConverter;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class PsrServerRequestParamConverterTest extends \PHPUnit_Framework_TestCase
{
    private $converter;

    public function setUp()
    {
        if (!class_exists('Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory')) {
            $this->markTestSkipped('The PSR-7 Bridge is not installed.');
        }

        $this->converter = new PsrServerRequestParamConverter(new DiactorosFactory());
    }

    public function testSupports()
    {
        $config = $this->createConfiguration('Psr\Http\Message\ServerRequestInterface');
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration('Psr\Http\Message\RequestInterface');
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration('Psr\Http\Message\HttpMessageInterface');
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($this->converter->supports($config));

        $config = $this->createConfiguration();
        $this->assertFalse($this->converter->supports($config));
    }

    public function testApply()
    {
        $request = new Request(
            array('foo' => 'bar'),
            array(),
            array(),
            array(),
            array(),
            array('HTTP_HOST' => 'dunglas.fr')
        );
        $config = $this->createConfiguration('Psr\Http\Message\ServerRequestInterface', 'request');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $request->attributes->get('request'));
        $this->assertEquals('bar', $request->query->get('foo'));
    }

    private function createConfiguration($class = null, $name = null)
    {
        $config = $this
            ->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
            ->setMethods(array('getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray', 'isOptional'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

        if (null !== $name) {
            $config->expects($this->any())
                ->method('getName')
                ->will($this->returnValue($name));
        }
        if (null !== $class) {
            $config->expects($this->any())
                ->method('getClass')
                ->will($this->returnValue($class));
        }

        return $config;
    }
}

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
 * @requires PHP 5.4
 */
class PsrServerRequestParamConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $converter = new PsrServerRequestParamConverter(new DiactorosFactory());
        $config = $this->createConfiguration('Psr\Http\Message\ServerRequestInterface');
        $this->assertTrue($converter->supports($config));

        $config = $this->createConfiguration('Psr\Http\Message\RequestInterface');
        $this->assertTrue($converter->supports($config));

        $config = $this->createConfiguration('Psr\Http\Message\MessageInterface');
        $this->assertTrue($converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($converter->supports($config));

        $config = $this->createConfiguration();
        $this->assertFalse($converter->supports($config));
    }

    public function testApply()
    {
        $converter = new PsrServerRequestParamConverter(new DiactorosFactory());
        $request = new Request(
            array('foo' => 'bar'),
            array(),
            array(),
            array(),
            array(),
            array('HTTP_HOST' => 'dunglas.fr')
        );
        $config = $this->createConfiguration('Psr\Http\Message\ServerRequestInterface', 'request');

        $converter->apply($request, $config);

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

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Routing;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Routing\Route as SymfonyRoute;

class AnnotatedRouteControllerLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testServiceOptionIsAllowedOnClass()
    {
        $route = $this->getMockBuilder('Symfony\Component\Routing\Route')
            ->setMethods(array('setDefault'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $route
            ->expects($this->once())
            ->method('setDefault')
            ->with('_controller', 'service:testServiceOptionIsAllowedOnClass')
        ;

        $annotation = new Route(array());
        $annotation->setService('service');

        $reader = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->setMethods(array('getClassAnnotation', 'getMethodAnnotations'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue($annotation))
        ;

        $reader
            ->expects($this->once())
            ->method('getMethodAnnotations')
            ->will($this->returnValue(array()))
        ;

        $loader = $this->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader')
            ->setConstructorArgs(array($reader))
            ->getMock()
        ;

        $r = new \ReflectionMethod($loader, 'configureRoute');
        $r->setAccessible(true);

        $r->invoke(
            $loader,
            $route,
            new \ReflectionClass($this),
            new \ReflectionMethod($this, 'testServiceOptionIsAllowedOnClass'),
            null
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The service option can only be specified at class level.
     */
    public function testServiceOptionIsNotAllowedOnMethod()
    {
        $route = $this->getMockBuilder('Symfony\Component\Routing\Route')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $reader = $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->setMethods(array('getClassAnnotation', 'getMethodAnnotations'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $annotation = new Route(array());
        $annotation->setService('service');

        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue(null))
        ;

        $reader
            ->expects($this->once())
            ->method('getMethodAnnotations')
            ->will($this->returnValue(array($annotation)))
        ;

        $loader = $this->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader')
            ->setConstructorArgs(array($reader))
            ->getMock()
        ;

        $r = new \ReflectionMethod($loader, 'configureRoute');
        $r->setAccessible(true);

        $r->invoke(
            $loader,
            $route,
            new \ReflectionClass($this),
            new \ReflectionMethod($this, 'testServiceOptionIsNotAllowedOnMethod'),
            null
        );
    }

    public function testLoad()
    {
        $loader = new AnnotatedRouteControllerLoader(new AnnotationReader());
        AnnotationRegistry::registerLoader('class_exists');

        $rc = $loader->load('Sensio\Bundle\FrameworkExtraBundle\Tests\Routing\Fixtures\FoobarController');

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $rc);
        $this->assertCount(2, $rc);

        $this->assertInstanceOf('Symfony\Component\Routing\Route', $rc->get('index'));
        $this->assertEquals(array('GET'), $rc->get('index')->getMethods());

        $this->assertInstanceOf('Symfony\Component\Routing\Route', $rc->get('new'));
        $this->assertEquals(array('POST'), $rc->get('new')->getMethods());
    }
}

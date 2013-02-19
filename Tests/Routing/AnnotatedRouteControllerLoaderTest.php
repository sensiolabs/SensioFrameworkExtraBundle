<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Routing;

use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
}

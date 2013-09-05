<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerParamConverterAtClassAndMethod;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ControllerListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->listener = new ControllerListener(new AnnotationReader());
        $this->request = $this->createRequest();

        // trigger the autoloading of the @Cache annotation
        class_exists('Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache');
    }

    public function tearDown()
    {
        $this->listener = null;
        $this->request = null;
    }

    public function testCacheAnnotationAtMethod()
    {
        $controller = new FooControllerCacheAtMethod();

        $this->event = $this->getFilterControllerEvent(array($controller, 'barAction'), $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    public function testCacheAnnotationAtClass()
    {
        $controller = new FooControllerCacheAtClass();
        $this->event = $this->getFilterControllerEvent(array($controller, 'barAction'), $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClass::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    public function testCacheAnnotationAtClassAndMethod()
    {
        $controller = new FooControllerCacheAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent(array($controller, 'barAction'), $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClassAndMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());

        $this->event = $this->getFilterControllerEvent(array($controller, 'bar2Action'), $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClassAndMethod::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    public function testMultipleAnnotationsOnMethod()
    {
        $controller = new FooControllerCacheAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent(array($controller, 'bar3Action'), $this->request);
        $this->listener->onKernelController($this->event);

        $annotation = $this->getReadedCache();
        $this->assertNotNull($annotation);
        $this->assertInstanceOf('Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache', $annotation);
        $this->assertEquals(FooControllerCacheAtClassAndMethod::METHOD_SMAXAGE, $annotation->getSMaxAge());
    }

    public function testMultipleParamConverterAnnotationsOnMethod()
    {
        $paramConverter = new \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter(array());
        $controller = new FooControllerParamConverterAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent(array($controller, 'barAction'), $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_converters');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter', $annotations[0]);
        $this->assertEquals('test', $annotations[0]->getName());

        $this->assertArrayHasKey(1, $annotations);
        $this->assertInstanceOf('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter', $annotations[1]);
        $this->assertEquals('test2', $annotations[1]->getName());

        $this->assertEquals(2, count($annotations));
    }

    protected function createRequest(Cache $cache = null)
    {
        return new Request(array(), array(), array(
            '_cache' => $cache,
        ));
    }

    protected function getFilterControllerEvent($controller, Request $request)
    {
        $mockKernel = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Kernel', array('', ''));

        return new FilterControllerEvent($mockKernel, $controller, $request, HttpKernelInterface::MASTER_REQUEST);
    }

    protected function getReadedCache()
    {
        return $this->request->attributes->get('_cache');
    }
}

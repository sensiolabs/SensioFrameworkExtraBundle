<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtMethod;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ControllerListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->listener = new ControllerListener(new AnnotationReader());
        $this->request = $this->createRequest();
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


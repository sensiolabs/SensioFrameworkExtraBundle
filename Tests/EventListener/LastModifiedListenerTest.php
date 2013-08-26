<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\LastModified;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\LastModifiedException;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\LastModifiedListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LastModifiedListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpKernelInterface
     */
    protected $kernel;

    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');

        $this->request  =  new Request();
        $params = new LastModified(array(
            'param'  => 'test',
            'method' => 'getDate'
        ));

        $this->request->attributes->set('_last_modified', $params);
        $this->request->attributes->set('test', new TestEntity());
        $this->request->headers->add(array('If-Modified-Since' => 'Fri, 23 Aug 2013 00:00:00 GMT'));

    }

    /**
     * @expectedException Sensio\Bundle\FrameworkExtraBundle\EventListener\LastModifiedException
     */
    public function testExceptionOnControllerEvent()
    {
        $listener           = new LastModifiedListener();
        $controllerEvent    = new FilterControllerEvent($this->kernel, array($this->getController(), 'execute'),  $this->request, null);

        $listener->onKernelController($controllerEvent);
    }

    public function testExceptionGetModifiedResponseAnother()
    {
        $event    = new GetResponseForExceptionEvent($this->kernel, $this->request, null, new LastModifiedException(500));
        $event->setResponse(new Response('Fake exception', 500));

        $controllerDefinedResponse = new Response();
        $controllerDefinedResponse->setStatusCode(304);

        $listener = new LastModifiedListener();

        $r = new \ReflectionProperty('Sensio\Bundle\FrameworkExtraBundle\EventListener\LastModifiedListener', 'response');
        $r->setAccessible(true);
        $r->setValue($listener, $controllerDefinedResponse);

        $listener->onKernelException($event);

        $this->assertEquals(304, $event->getResponse()->getStatusCode());
    }

    public function testLastModifiedHeaderInResponse()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $response = new Response();

        $listener = new LastModifiedListener();
        $r = new \ReflectionProperty('Sensio\Bundle\FrameworkExtraBundle\EventListener\LastModifiedListener', 'lastModifiedDate');
        $r->setAccessible(true);
        $r->setValue($listener, new \DateTime());

        $event = new FilterResponseEvent($this->kernel, $request, null, $response);
        $listener->onKernelResponse($event);

        $this->assertTrue($event->getResponse()->headers->has('Last-Modified'));
    }

    protected function getController()
    {
        return $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Controller\Controller')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();
    }
}

class TestEntity
{
    public function getDate()
    {
        $pastTime = new \DateTime('Fri, 23 Aug 2013 00:00:00 GMT');
        return $pastTime;
    }
}
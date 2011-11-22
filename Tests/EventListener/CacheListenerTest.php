<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\CacheListener;

class CacheListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->listener = new CacheListener();
        $this->response = new Response();
        $this->cache = new Cache(array());
        $this->request = $this->createRequest($this->cache);
        $this->event = $this->createEventMock($this->request, $this->response);
    }

    public function testWontReassignResponseWhenResponseIsUnsuccessful()
    {
        $this->event
            ->expects($this->never())
            ->method('setResponse')
        ;

        $this->response->setStatusCode(404);

        $this->assertInternalType('null', $this->listener->onKernelResponse($this->event));
    }

    public function testWontReassignResponseWhenNoConfigurationIsPresent()
    {
        $this->event
            ->expects($this->never())
            ->method('setResponse')
        ;

        $this->request->attributes->remove('_cache');

        $this->assertInternalType('null', $this->listener->onKernelResponse($this->event));
    }

    public function testResponseIsPublicIfConfigurationIsPublic()
    {
        $request = $this->createRequest(new Cache(array(
            'public' => true,
        )));

        $this->listener->onKernelResponse($this->createEventMock($request, $this->response));

        $this->assertTrue($this->response->headers->hasCacheControlDirective('public'));
        $this->assertFalse($this->response->headers->hasCacheControlDirective('private'));
    }

    public function testConfigurationAttributesAreSetOnResponse()
    {
        $this->assertInternalType('null', $this->response->getMaxAge());
        $this->assertInternalType('null', $this->response->getExpires());
        $this->assertFalse($this->response->headers->hasCacheControlDirective('s-maxage'));

        $this->request->attributes->set('_cache', new Cache(array(
            'expires' => 'tomorrow',
            'smaxage' => '15',
            'maxage' => '15',
        )));

        $this->listener->onKernelResponse($this->event);

        $this->assertEquals('15', $this->response->getMaxAge());
        $this->assertEquals('15', $this->response->headers->getCacheControlDirective('s-maxage'));
        $this->assertInstanceOf('DateTime', $this->response->getExpires());
    }

    protected function createRequest(Cache $cache = null)
    {
        return new Request(array(), array(), array(
            '_cache' => $cache,
        ));
    }

    protected function createEventMock(Request $request, Response $response)
    {
        $event = $this->getMock('Symfony\Component\HttpKernel\Event\FilterResponseEvent', array(), array(), '', null);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request))
        ;

        $event
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response))
        ;

        return $event;
    }
}

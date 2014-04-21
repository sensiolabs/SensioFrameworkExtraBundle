<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener;

class HttpCacheListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->listener = new HttpCacheListener();
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

        $this->response->setStatusCode(500);

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

    public function testLastModifiedNotModifiedResponse()
    {
        $request = $this->createRequest(new Cache(array('lastModified' => 'test.getDate()')));
        $request->attributes->set('test', new TestEntity());
        $request->headers->add(array('If-Modified-Since' => 'Fri, 23 Aug 2013 00:00:00 GMT'));

        $listener = new HttpCacheListener();
        $controllerEvent = new FilterControllerEvent($this->getKernel(), function () { return new Response(500); },  $request, null);

        $listener->onKernelController($controllerEvent);
        $response = call_user_func($controllerEvent->getController());

        $this->assertEquals(304, $response->getStatusCode());
    }

    public function testLastModifiedHeader()
    {
        $request = $this->createRequest(new Cache(array('lastModified' => 'test.getDate()')));
        $request->attributes->set('test', new TestEntity());
        $response = new Response();

        $listener = new HttpCacheListener();
        $controllerEvent = new FilterControllerEvent($this->getKernel(), function () { return new Response(); }, $request, null);
        $listener->onKernelController($controllerEvent);

        $responseEvent = new FilterResponseEvent($this->getKernel(), $request, null, call_user_func($controllerEvent->getController()));
        $listener->onKernelResponse($responseEvent);

        $response = $responseEvent->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Last-Modified'));
        $this->assertEquals('Fri, 23 Aug 2013 00:00:00 GMT', $response->headers->get('Last-Modified'));
    }

    public function testETagNotModifiedResponse()
    {
        $request = $this->createRequest(new Cache(array('etag' => 'test.getId()')));
        $request->attributes->set('test', $entity = new TestEntity());
        $request->headers->add(array('If-None-Match' => sprintf('"%s"', hash('sha256', $entity->getId()))));

        $listener = new HttpCacheListener();
        $controllerEvent = new FilterControllerEvent($this->getKernel(), function () { return new Response(500); },  $request, null);

        $listener->onKernelController($controllerEvent);
        $response = call_user_func($controllerEvent->getController());

        $this->assertEquals(304, $response->getStatusCode());
    }

    public function testETagHeader()
    {
        $request = $this->createRequest(new Cache(array('ETag' => 'test.getId()')));
        $request->attributes->set('test', $entity = new TestEntity());
        $response = new Response();

        $listener = new HttpCacheListener();
        $controllerEvent = new FilterControllerEvent($this->getKernel(), function () { return new Response(); }, $request, null);
        $listener->onKernelController($controllerEvent);

        $responseEvent = new FilterResponseEvent($this->getKernel(), $request, null, call_user_func($controllerEvent->getController()));
        $listener->onKernelResponse($responseEvent);

        $response = $responseEvent->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->has('ETag'));
        $this->assertContains(hash('sha256', $entity->getId()), $response->headers->get('ETag'));
    }

    private function createRequest(Cache $cache = null)
    {
        return new Request(array(), array(), array(
            '_cache' => $cache,
        ));
    }

    private function createEventMock(Request $request, Response $response)
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

    private function getKernel()
    {
        return $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    }
}

class TestEntity
{
    public function getDate()
    {
        return new \DateTime('Fri, 23 Aug 2013 00:00:00 GMT');
    }

    public function getId()
    {
        return '12345';
    }
}

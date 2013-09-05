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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\LastModified;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\LastModifiedListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LastModifiedListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testNotModifiedResponse()
    {
        $request = $this->getRequest();
        $request->headers->add(array('If-Modified-Since' => 'Fri, 23 Aug 2013 00:00:00 GMT'));

        $listener = new LastModifiedListener();
        $controllerEvent = new FilterControllerEvent($this->getKernel(), function () { return new Response(500); },  $request, null);

        $listener->onKernelController($controllerEvent);
        $response = call_user_func($controllerEvent->getController());

        $this->assertEquals(304, $response->getStatusCode());
    }

    public function testLastModifiedHeader()
    {
        $request = $this->getRequest();
        $response = new Response();

        $listener = new LastModifiedListener();
        $controllerEvent = new FilterControllerEvent($this->getKernel(), function () { return new Response(); }, $request, null);
        $listener->onKernelController($controllerEvent);

        $responseEvent = new FilterResponseEvent($this->getKernel(), $request, null, call_user_func($controllerEvent->getController()));
        $listener->onKernelResponse($responseEvent);

        $response = $responseEvent->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Last-Modified'));
        $this->assertEquals('Fri, 23 Aug 2013 00:00:00 GMT', $response->headers->get('Last-Modified'));
    }

    private function getRequest()
    {
        $request = Request::create('/');
        $request->attributes->set('_last_modified', new LastModified(array('param' => 'test', 'method' => 'getDate')));
        $request->attributes->set('test', new TestEntity());

        return $request;
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
}

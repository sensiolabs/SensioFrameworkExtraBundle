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

use Sensio\Bundle\FrameworkExtraBundle\EventListener\PsrResponseListener;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Tests\Fixtures\Response;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @requires PHP 5.4
 */
class PsrResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertsControllerResult()
    {
        $listener = new PsrResponseListener(new HttpFoundationFactory());
        $event = $this->createEventMock(new Response());
        $event->expects($this->once())->method('setResponse')->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Response'));
        $listener->onKernelView($event);
    }

    public function testDoesNotConvertControllerResult()
    {
        $listener = new PsrResponseListener(new HttpFoundationFactory());
        $event = $this->createEventMock(array());
        $event->expects($this->never())->method('setResponse');

        $listener->onKernelView($event);

        $event = $this->createEventMock(null);
        $event->expects($this->never())->method('setResponse');

        $listener->onKernelView($event);
    }

    private function createEventMock($controllerResult)
    {
        $event = $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent', array(), array(), '', null);
        $event
            ->expects($this->any())
            ->method('getControllerResult')
            ->will($this->returnValue($controllerResult))
        ;

        return $event;
    }
}

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
 */
class PsrResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PsrResponseListener
     */
    private $listener;

    public function setUp()
    {
        if (!class_exists('Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface')) {
            $this->markTestSkipped('The PSR-7 Bridge is not installed.');
        }

        $this->listener = new PsrResponseListener(new HttpFoundationFactory());
    }

    public function testConvertsControllerResult()
    {
        $event = $this->createEventMock(new Response());
        $event->expects($this->once())->method('setResponse')->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Response'));
        $this->listener->onKernelView($event);
    }

    public function testDoesNotConvertControllerResult()
    {
        $event = $this->createEventMock(array());
        $event->expects($this->never())->method('setResponse');

        $this->listener->onKernelView($event);

        $event = $this->createEventMock(null);
        $event->expects($this->never())->method('setResponse');

        $this->listener->onKernelView($event);
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

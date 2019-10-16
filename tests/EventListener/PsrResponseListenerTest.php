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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @requires PHP 5.4
 */
class PsrResponseListenerTest extends \PHPUnit\Framework\TestCase
{
    public function testConvertsControllerResult()
    {
        $listener = new PsrResponseListener(new HttpFoundationFactory());
        $event = $this->createEventMock(new Response());
        $listener->onKernelView($event);
        $this->assertTrue($event->hasResponse());
    }

    public function testDoesNotConvertControllerResult()
    {
        $listener = new PsrResponseListener(new HttpFoundationFactory());
        $event = $this->createEventMock([]);

        $listener->onKernelView($event);
        $this->assertFalse($event->hasResponse());

        $event = $this->createEventMock(null);

        $listener->onKernelView($event);
        $this->assertFalse($event->hasResponse());
    }

    private function createEventMock($controllerResult)
    {
        return new ViewEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $controllerResult);
    }
}

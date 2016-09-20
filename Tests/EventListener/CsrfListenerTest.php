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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Csrf;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\CsrfListener;
use Sensio\Bundle\FrameworkExtraBundle\Exception\InvalidCsrfTokenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Csrf\CsrfToken;

class CsrfListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Sensio\Bundle\FrameworkExtraBundle\Exception\InvalidCsrfTokenException
     */
    public function testInvalidToken()
    {
        $tokenManager = $this->getTokenManager();
        $listener = new CsrfListener($tokenManager);

        $tokenManager
            ->expects($this->once())
            ->method('isTokenValid')
            ->with(
                $this->callback(
                    function (CsrfToken $token) {
                        return '' === $token->getValue() && 'post_delete' === $token->getId();
                    }
                )
            )
            ->will($this->returnValue(false));

        $tokenManager
            ->expects($this->once())
            ->method('getToken')
            ->with($this->equalTo('post_delete'))
            ->will($this->returnValue(new CsrfToken('post_delete', 'abcd')));

        $request = $this->createRequest(new Csrf(array('intention' => 'post_delete')));
        $event = $this->createEvent($request);

        $listener->onKernelController($event);
    }

    public function testValidToken()
    {
        $tokenManager = $this->getTokenManager();
        $listener = new CsrfListener($tokenManager);

        $tokenManager
            ->expects($this->once())
            ->method('isTokenValid')
            ->with(
                $this->callback(
                    function (CsrfToken $token) {
                        return 'abcd' === $token->getValue() && 'post_delete' === $token->getId();
                    }
                )
            )
            ->will($this->returnValue(true));

        $request = $this->createRequest(new Csrf(array('intention' => 'post_delete')), array('_token' => 'abcd'));
        $event = $this->createEvent($request);

        try {
            $listener->onKernelController($event);
        } catch (InvalidCsrfTokenException $e) {
            $this->fail();
        }
    }

    private function createRequest(Csrf $csrf = null, array $query = array())
    {
        return new Request($query, array(), array('_csrf' => $csrf));
    }

    private function createEvent(Request $request)
    {
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();

        return new FilterControllerEvent(
            $kernel,
            function () {
                return new Response();
            },
            $request,
            null
        );
    }

    private function getTokenManager()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface')
            ->getMock();
    }
}

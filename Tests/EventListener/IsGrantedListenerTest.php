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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\IsGrantedListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IsGrantedListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testExceptionIfSecurityNotInstalled()
    {
        $listener = new IsGrantedListener();
        $request = $this->createRequest(new IsGranted(array()));
        $listener->onKernelController($this->createFilterControllerEvent($request));
    }

    public function testNothingHappensWithNoConfig()
    {
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $authChecker->expects($this->never())
            ->method('isGranted');

        $listener = new IsGrantedListener($authChecker);
        $request = $this->createRequest();
        $listener->onKernelController($this->createFilterControllerEvent($request));
    }

    public function testIsGrantedCalledCorrectly()
    {
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        // createRequest() puts 2 IsGranted annotations into the config
        $authChecker->expects($this->exactly(2))
            ->method('isGranted')
            ->with('ROLE_ADMIN', 'bar')
            ->will($this->returnValue(true));

        $listener = new IsGrantedListener($authChecker);
        $isGranted = new IsGranted(array('attributes' => 'ROLE_ADMIN', 'subject' => 'foo'));
        $request = $this->createRequest($isGranted);
        $request->attributes->set('foo', 'bar');
        $listener->onKernelController($this->createFilterControllerEvent($request));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExceptionWhenMissingSubjectAttribute()
    {
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();

        $listener = new IsGrantedListener($authChecker);
        $isGranted = new IsGranted(array('attributes' => 'ROLE_ADMIN', 'subject' => 'non_existent'));
        $request = $this->createRequest($isGranted);
        $listener->onKernelController($this->createFilterControllerEvent($request));
    }

    /**
     * @dataProvider getAccessDeniedMessageTests
     */
    public function testAccessDeniedMessages(array $attributes, $subject, $expectedMessage)
    {
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $authChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(false));

        $listener = new IsGrantedListener($authChecker);
        $isGranted = new IsGranted(array('attributes' => $attributes, 'subject' => $subject));
        $request = $this->createRequest($isGranted);

        // avoid the error of the subject not being found in the request attributes
        if (null !== $subject) {
            $request->attributes->set($subject, 'bar');
        }

        $this->setExpectedException(AccessDeniedException::class, $expectedMessage);

        $listener->onKernelController($this->createFilterControllerEvent($request));
    }

    public function getAccessDeniedMessageTests()
    {
        yield array(array('ROLE_ADMIN'), null, 'Access Denied by controller annotation @IsGranted("ROLE_ADMIN")');
        yield array(array('ROLE_ADMIN', 'ROLE_USER'), null, 'Access Denied by controller annotation @IsGranted(["ROLE_ADMIN", "ROLE_USER"])');
        yield array(array('ROLE_ADMIN', 'ROLE_USER'), 'product', 'Access Denied by controller annotation @IsGranted(["ROLE_ADMIN", "ROLE_USER"], product)');
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage Not found
     */
    public function testNotFoundHttpException()
    {
        $request = $this->createRequest(new IsGranted(array('attributes' => 'ROLE_ADMIN', 'statusCode' => 404, 'message' => 'Not found')));
        $event = $this->createFilterControllerEvent($request);

        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $authChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(false));

        $listener = new IsGrantedListener($authChecker);
        $listener->onKernelController($event);
    }

    private function createRequest(IsGranted $isGranted = null)
    {
        return new Request(array(), array(), array(
            '_is_granted' => null === $isGranted ? array() : array(
                $isGranted,
                $isGranted,
            ),
        ));
    }

    private function createFilterControllerEvent(Request $request)
    {
        return new FilterControllerEvent($this->getMockBuilder(HttpKernelInterface::class)->getMock(), function () { return new Response(); }, $request, null);
    }
}

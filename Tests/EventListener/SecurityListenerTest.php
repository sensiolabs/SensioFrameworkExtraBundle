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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener;
use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SecurityListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @group legacy
     */
    public function testLegacyAccessDenied()
    {
        if (!interface_exists('Symfony\Component\Security\Core\SecurityContextInterface')) {
            $this->markTestSkipped();
        }

        $listener = $this->createListenerWithLegacySecurityContext();
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN") or is_granted("FOO")')));

        $event = new FilterControllerEvent($this->getKernel(), function () { return new Response(); }, $request, null);

        $listener->onKernelController($event);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testAccessDenied()
    {
        if (!interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $this->markTestSkipped();
        }

        $listener = $this->createSecurityListener();
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN") or is_granted("FOO")')));

        $event = new FilterControllerEvent($this->getKernel(), function () { return new Response(); }, $request, null);

        $listener->onKernelController($event);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage Test Access Denied Message
     * @group legacy
     */
    public function testLegacyExceptionMessage()
    {
        if (!interface_exists('Symfony\Component\Security\Core\SecurityContextInterface')) {
            $this->markTestSkipped();
        }

        $listener = $this->createListenerWithLegacySecurityContext();
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN") or is_granted("FOO")', 'message' => 'Test Access Denied Message')));

        $event = new FilterControllerEvent($this->getKernel(), function () { return new Response(); }, $request, null);

        $listener->onKernelController($event);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage Test Access Denied Message
     */
    public function testExceptionMessage()
    {
        if (!interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $this->markTestSkipped();
        }

        $listener = $this->createSecurityListener();
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN") or is_granted("FOO")', 'message' => 'Test Access Denied Message')));

        $event = new FilterControllerEvent($this->getKernel(), function () { return new Response(); }, $request, null);

        $listener->onKernelController($event);
    }

    /**
     * @param Security|null $security
     *
     * @return Request
     */
    private function createRequest(Security $security = null)
    {
        return new Request(array(), array(), array('_security' => $security));
    }

    /**
     * @return HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getKernel()
    {
        return $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
    }

    /**
     * @return SecurityListener
     */
    private function createSecurityListener()
    {
        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')->getMock();
        $token->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')->getMock();
        $tokenStorage->expects($this->exactly(2))->method('getToken')->will($this->returnValue($token));

        $authChecker = $this->getMockBuilder('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')->getMock();
        $authChecker->expects($this->once())->method('isGranted')->willReturn(false);

        $trustResolver = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface')->getMock();

        $language = new ExpressionLanguage();

        return new SecurityListener(null, $language, $trustResolver, null, $tokenStorage, $authChecker);
    }

    /**
     * @return SecurityListener
     */
    private function createListenerWithLegacySecurityContext()
    {
        $this->iniSet('error_reporting', -1 & ~E_USER_DEPRECATED);

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')->getMock();
        $token->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')->getMock();
        $securityContext->expects($this->once())->method('isGranted')->willReturn(false);
        $securityContext->expects($this->exactly(2))->method('getToken')->will($this->returnValue($token));

        $trustResolver = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface')->getMock();

        $language = new ExpressionLanguage();

        return new SecurityListener($securityContext, $language, $trustResolver);
    }
}

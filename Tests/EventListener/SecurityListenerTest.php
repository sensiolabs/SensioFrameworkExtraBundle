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

use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        $this->iniSet('error_reporting', -1 & ~E_USER_DEPRECATED);

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')->getMock();
        $token->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')->getMock();
        $securityContext->expects($this->once())->method('isGranted')->will($this->throwException(new AccessDeniedException()));
        $securityContext->expects($this->exactly(2))->method('getToken')->will($this->returnValue($token));

        $trustResolver = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface')->getMock();

        $language = new ExpressionLanguage();

        $listener = new SecurityListener($securityContext, $language, $trustResolver);
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN") or is_granted("FOO")')));

        $event = new FilterControllerEvent($this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock(), function () { return new Response(); }, $request, null);

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

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')->getMock();
        $token->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')->getMock();
        $tokenStorage->expects($this->exactly(2))->method('getToken')->will($this->returnValue($token));

        $authChecker = $this->getMockBuilder('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')->getMock();
        $authChecker->expects($this->once())->method('isGranted')->will($this->throwException(new AccessDeniedException()));

        $trustResolver = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface')->getMock();

        $language = new ExpressionLanguage();

        $listener = new SecurityListener(null, $language, $trustResolver, null, $tokenStorage, $authChecker);
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN") or is_granted("FOO")')));

        $event = new FilterControllerEvent($this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock(), function () { return new Response(); }, $request, null);

        $listener->onKernelController($event);
    }

    private function createRequest(Security $security = null)
    {
        return new Request(array(), array(), array(
            '_security' => $security,
        ));
    }

    private function getKernel()
    {
        return $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
    }
}

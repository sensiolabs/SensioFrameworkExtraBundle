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
     */
    public function testAccessDenied()
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext->expects($this->once())->method('isGranted')->will($this->throwException(new AccessDeniedException()));
        $securityContext->expects($this->once())->method('getToken')->will($this->returnValue($token));

        $trustResolver = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface');

        $language = new ExpressionLanguage();

        $listener = new SecurityListener($securityContext, $language, $trustResolver);
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN") or is_granted("FOO")')));

        $event = new FilterControllerEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), function () { return new Response(); }, $request, null);

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
        return $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    }
}

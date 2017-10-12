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

use Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter;
use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;

class SecurityListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testAccessDenied()
    {
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN") or is_granted("FOO")')));
        $event = new FilterControllerArgumentsEvent($this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock(), function () { return new Response(); }, array(), $request, null);

        $this->getListener()->onKernelControllerArguments($event);
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage Not found
     */
    public function testNotFoundHttpException()
    {
        $request = $this->createRequest(new Security(array('expression' => 'has_role("ROLE_ADMIN") or is_granted("FOO")', 'statusCode' => 404, 'message' => 'Not found')));
        $event = new FilterControllerArgumentsEvent($this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock(), function () { return new Response(); }, array(), $request, null);

        $this->getListener()->onKernelControllerArguments($event);
    }

    private function getListener()
    {
        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')->getMock();
        $token->expects($this->once())->method('getRoles')->will($this->returnValue(array()));

        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')->getMock();
        $tokenStorage->expects($this->exactly(2))->method('getToken')->will($this->returnValue($token));

        $authChecker = $this->getMockBuilder('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')->getMock();
        $authChecker->expects($this->once())->method('isGranted')->will($this->returnValue(false));

        $trustResolver = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface')->getMock();

        $argNameConverter = $this->createArgumentNameConverter(array());

        $language = new ExpressionLanguage();

        return new SecurityListener($argNameConverter, $language, $trustResolver, null, $tokenStorage, $authChecker);
    }

    private function createRequest(Security $security = null)
    {
        return new Request(array(), array(), array(
            '_security' => array(
                $security,
                $security,
            ),
        ));
    }

    private function createArgumentNameConverter(array $arguments)
    {
        $nameConverter = $this->getMockBuilder(ArgumentNameConverter::class)->disableOriginalConstructor()->getMock();

        $nameConverter->expects($this->any())
            ->method('getControllerArguments')
            ->will($this->returnValue($arguments));

        return $nameConverter;
    }
}

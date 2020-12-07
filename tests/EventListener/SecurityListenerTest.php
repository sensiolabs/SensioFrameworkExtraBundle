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
use Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter;
use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class SecurityListenerTest extends \PHPUnit\Framework\TestCase
{
    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $request = $this->createRequest(new Security(['expression' => 'is_granted("ROLE_ADMIN") or is_granted("FOO")']));
        $event = new ControllerArgumentsEvent($this->getMockBuilder(HttpKernelInterface::class)->getMock(), function () {
            return new Response();
        }, [], $request, null);

        $this->getListener()->onKernelControllerArguments($event);
    }

    public function testNotFoundHttpException()
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Not found');

        $request = $this->createRequest(new Security(['expression' => 'is_granted("ROLE_ADMIN") or is_granted("FOO")', 'statusCode' => 404, 'message' => 'Not found']));
        $event = new ControllerArgumentsEvent($this->getMockBuilder(HttpKernelInterface::class)->getMock(), function () {
            return new Response();
        }, [], $request, null);

        $this->getListener()->onKernelControllerArguments($event);
    }

    private function getListener()
    {
        $roleHierarchy = $this->getMockBuilder(RoleHierarchy::class)->disableOriginalConstructor()->getMock();
        $roleHierarchy->expects($this->once())->method('getReachableRoleNames')->willReturn([]);

        $token = $this->getMockBuilder(AbstractToken::class)->getMock();
        $token->expects($this->once())->method('getRoleNames')->willReturn([]);

        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $tokenStorage->expects($this->exactly(2))->method('getToken')->willReturn($token);

        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $authChecker->expects($this->exactly(2))->method('isGranted')->willReturn(false);

        $trustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();

        $argNameConverter = $this->createArgumentNameConverter([]);

        $language = new ExpressionLanguage();

        return new SecurityListener($argNameConverter, $language, $trustResolver, $roleHierarchy, $tokenStorage, $authChecker);
    }

    private function createRequest(Security $security = null)
    {
        return new Request([], [], [
            '_security' => [
                $security,
                $security,
            ],
        ]);
    }

    private function createArgumentNameConverter(array $arguments)
    {
        $nameConverter = $this->getMockBuilder(ArgumentNameConverter::class)->disableOriginalConstructor()->getMock();

        $nameConverter->expects($this->any())
            ->method('getControllerArguments')
            ->willReturn($arguments);

        return $nameConverter;
    }
}

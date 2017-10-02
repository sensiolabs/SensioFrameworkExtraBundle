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
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
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
        $listener = new IsGrantedListener($this->createArgumentMetadataFactory(array()));
        $request = $this->createRequest(new IsGranted(array()));
        $listener->onKernelControllerArguments($this->createFilterControllerEvent($request, array()));
    }

    public function testNothingHappensWithNoConfig()
    {
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $authChecker->expects($this->never())
            ->method('isGranted');

        $listener = new IsGrantedListener($this->createArgumentMetadataFactory(array()), $authChecker);
        $request = $this->createRequest();
        $listener->onKernelControllerArguments($this->createFilterControllerEvent($request, array()));
    }

    public function testIsGrantedCalledCorrectly()
    {
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        // createRequest() puts 2 IsGranted annotations into the config
        $authChecker->expects($this->exactly(2))
            ->method('isGranted')
            ->with('ROLE_ADMIN', 'bar')
            ->will($this->returnValue(true));

        $listener = new IsGrantedListener($this->createArgumentMetadataFactory(array()), $authChecker);
        $isGranted = new IsGranted(array('attributes' => 'ROLE_ADMIN', 'subject' => 'foo'));
        $request = $this->createRequest($isGranted);
        $request->attributes->set('foo', 'bar');
        $listener->onKernelControllerArguments($this->createFilterControllerEvent($request, array()));
    }

    public function testIsGrantedSubjectFromArguments()
    {
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        // createRequest() puts 2 IsGranted annotations into the config
        $authChecker->expects($this->exactly(2))
            ->method('isGranted')
            // the subject => arg2name will eventually resolve to the 2nd argument, which has this value
            ->with('ROLE_ADMIN', 'arg2Value')
            ->will($this->returnValue(true));

        // create metadata for 2 named args for the controller
        $arg1Metadata = new ArgumentMetadata('arg1Name', 'string', false, false, null);
        $arg2Metadata = new ArgumentMetadata('arg2Name', 'string', false, false, null);
        $listener = new IsGrantedListener($this->createArgumentMetadataFactory(array($arg1Metadata, $arg2Metadata)), $authChecker);
        $isGranted = new IsGranted(array('attributes' => 'ROLE_ADMIN', 'subject' => 'arg2Name'));
        $request = $this->createRequest($isGranted);

        // the 2 resolved arguments to the controller
        $arguments = array('arg1Value', 'arg2Value');
        $listener->onKernelControllerArguments($this->createFilterControllerEvent($request, $arguments));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExceptionWhenMissingSubjectAttribute()
    {
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();

        $listener = new IsGrantedListener($this->createArgumentMetadataFactory(array()), $authChecker);
        $isGranted = new IsGranted(array('attributes' => 'ROLE_ADMIN', 'subject' => 'non_existent'));
        $request = $this->createRequest($isGranted);
        $listener->onKernelControllerArguments($this->createFilterControllerEvent($request, array()));
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

        $listener = new IsGrantedListener($this->createArgumentMetadataFactory(array()), $authChecker);
        $isGranted = new IsGranted(array('attributes' => $attributes, 'subject' => $subject));
        $request = $this->createRequest($isGranted);

        // avoid the error of the subject not being found in the request attributes
        if (null !== $subject) {
            $request->attributes->set($subject, 'bar');
        }

        $this->setExpectedException(AccessDeniedException::class, $expectedMessage);

        $listener->onKernelControllerArguments($this->createFilterControllerEvent($request, array()));
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
        $event = $this->createFilterControllerEvent($request, array());

        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $authChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(false));

        $listener = new IsGrantedListener($this->createArgumentMetadataFactory(array()), $authChecker);
        $listener->onKernelControllerArguments($event);
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

    private function createFilterControllerEvent(Request $request, array $arguments)
    {
        return new FilterControllerArgumentsEvent($this->getMockBuilder(HttpKernelInterface::class)->getMock(), function () { return new Response(); }, $arguments, $request, null);
    }

    private function createArgumentMetadataFactory(array $argumentMetadatas)
    {
        $factory = $this->getMockBuilder(ArgumentMetadataFactoryInterface::class)->getMock();

        $factory->expects($this->any())
            ->method('createArgumentMetadata')
            ->will($this->returnValue($argumentMetadatas));

        return $factory;
    }
}

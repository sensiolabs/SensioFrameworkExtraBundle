<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\CurrentUserParamConverter;

class CurrentUserParamConverterTest extends \PHPUnit_Framework_TestCase
{
    private $converter;

    public function setUp()
    {
        $this->converter = new CurrentUserParamConverter(
            $this->getSecurityContextWithUser(null)
        );
    }

    public function testThrowsExceptionWhenSecurityBundleNotEnabled()
    {
        $this->setExpectedException('LogicException');
        new CurrentUserParamConverter(null);
    }

    public function testSupports()
    {
        $config = $this->createConfiguration('Symfony\Component\Security\Core\User\UserInterface');
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($this->converter->supports($config));

    }

    public function testApply()
    {
        $expectedUser = $this->getMockUser();
        $this->converter = new CurrentUserParamConverter(
            $this->getSecurityContextWithUser($expectedUser)
        );

        $config = $this->createConfiguration('Symfony\Component\Security\Core\User\UserInterface', 'currentUser');

        $request = new Request();

        $this->converter->apply($request, $config);

        $this->assertEquals($expectedUser, $request->attributes->get('currentUser'));
    }

    public function testThrowsExceptionWhenRequiredUserNotLoggedIn()
    {
        $this->converter = new CurrentUserParamConverter(
            $this->getSecurityContextWithUser(null)
        );

        $config = $this->createConfiguration('Symfony\Component\Security\Core\User\UserInterface', 'currentUser');

        $request = new Request();


        $this->setExpectedException('LogicException');
        $this->converter->apply($request, $config);

    }

    public function testMissingUserNotSetIfNotRequired()
    {
        $this->converter = new CurrentUserParamConverter(
            $this->getSecurityContextWithUser(null)
        );

        $config = $this->createConfiguration('Symfony\Component\Security\Core\User\UserInterface', 'currentUser');

        $config->expects($this->any())
            ->method('isOptional')
            ->will($this->returnValue(true));

        $request = new Request();

        $this->converter->apply($request, $config);

    }

    private function createConfiguration($class = null, $name = null)
    {
        $config = $this
            ->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
            ->setMethods(array('getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray', 'isOptional'))
            ->disableOriginalConstructor()
            ->getMock();

        if ($name !== null) {
            $config->expects($this->any())
                ->method('getName')
                ->will($this->returnValue($name));
        }
        if ($class !== null) {
            $config->expects($this->any())
                ->method('getClass')
                ->will($this->returnValue($class));
        }

        return $config;
    }


    private function getSecurityContextWithUser($user = null)
    {
        $tokenMock = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->getMock();

        if ($user !== null) {

            $tokenMock->expects($this->any())
                ->method('getUser')
                ->will($this->returnValue($user));
        }

        $mock = $this
            ->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->getMock();

        $mock->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($tokenMock));

        return $mock;

    }

    private function getMockUser()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->getMock();
    }
}
<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */


namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;

class DoctrineParamConverterTest extends \PHPUnit_Framework_TestCase
{
    const PARAMETER_NAME = 'test';

    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $registry;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $repository;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    private $metadata;

    /**
     * @var DoctrineParamConverter
     */
    private $converter;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
            $this->markTestSkipped();
        }

        $this->registry  = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->converter = new DoctrineParamConverter($this->registry);

        $this->metadataFactory = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');

        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->objectManager->expects($this->any())
            ->method('getMetadataFactory')
            ->will($this->returnValue($this->metadataFactory));

        $this->metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $this->metadata->expects($this->any())
            ->method('hasField')
            ->will($this->returnCallback(function($name) {
            if ('user' === $name) {
                return true;
            }

            return false;
        }));

        $this->metadata->expects($this->any())
            ->method('hasAssociation')
            ->will($this->returnCallback(function($name) {
            if ('group' === $name) {
                return true;
            }

            return false;
        }));

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue($this->metadata));

        $this->registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager));

        $this->repository = $this->getMock('Sensio\Bundle\FrameworkExtraBundle\Tests\Fixtures\TestRepository');

        $this->registry->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue($this->repository));
    }

    protected function createConfiguration(array $options = array())
    {
        $config = $this->getMock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface', array(
            'getClass', 'getAliasName', 'getName', 'getOptions', 'isOptional'
        ));

        $config->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $config->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('stdClass'));

        $config->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::PARAMETER_NAME));

        return $config;
    }

    public function testApplyWithNoIdAndData()
    {
        $request = new Request();
        $config  = $this->createConfiguration();

        $this->setExpectedException('LogicException');
        $this->converter->apply($request, $config);
    }

    public function testSupports()
    {
        $config = $this->createConfiguration();

        $this->metadataFactory->expects($this->once())
            ->method('isTransient')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue(false));

        $ret = $this->converter->supports($config);

        $this->assertTrue($ret, "Should be supported");
    }

    public function testApplyWithId()
    {
        $request = new Request();
        $config  = $this->createConfiguration();

        $request->attributes->set('id', '42');

        $entity = new \stdClass();

        $this->repository->expects($this->once())
            ->method('find')
            ->with($this->equalTo('42'))
            ->will($this->returnValue($entity));

        $this->assertTrue($this->converter->apply($request, $config));
        $this->assertEquals($entity, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWithName()
    {
        $request = new Request();
        $config  = $this->createConfiguration();

        $request->attributes->set(self::PARAMETER_NAME, '42');

        $entity = new \stdClass();

        $this->repository->expects($this->once())
            ->method('find')
            ->with($this->equalTo('42'))
            ->will($this->returnValue($entity));

        $this->assertTrue($this->converter->apply($request, $config));
        $this->assertEquals($entity, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWithCriterias()
    {
        $request = new Request();
        $config  = $this->createConfiguration();

        $criterias = array(
            'user' => 1,
            'group' => 2,
        );

        $request->attributes->replace($criterias);

        $entity = new \stdClass();

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array(
                'user' => 1,
                'group' => 2,
            )))
            ->will($this->returnValue($entity));

        $this->assertTrue($this->converter->apply($request, $config));
        $this->assertEquals($entity, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWithPrefixedCriterias()
    {
        $request = new Request();
        $config  = $this->createConfiguration();

        $request->attributes->replace(array(
            self::PARAMETER_NAME . '_user' => 1,
            self::PARAMETER_NAME . '_group' => 2,
        ));

        $entity = new \stdClass();

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array(
                'user' => 1,
                'group' => 2,
            )))
            ->will($this->returnValue($entity));

        $this->assertTrue($this->converter->apply($request, $config));
        $this->assertEquals($entity, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testCustomMethod()
    {
        $request = new Request();
        $config = $this->createConfiguration(array(
            'method' => 'customMethod',
        ));

        $request->attributes->set('group', 'group');
        $request->attributes->set('user', 'user');

        $entity = new \stdClass();

        $this->repository->expects($this->once())
            ->method('customMethod')
            ->with($this->equalTo('group'), $this->equalTo('user'))
            ->will($this->returnValue($entity));

        $this->assertTrue($this->converter->apply($request, $config));
        $this->assertEquals($entity, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testArrayInCustomMethod()
    {
        $request = new Request();
        $config = $this->createConfiguration(array(
            'method' => 'customArrayMethod',
        ));

        $request->attributes->set('users', 'users');

        $this->setExpectedException('LogicException');
        $this->converter->apply($request, $config);
    }

    public function testClassInCustomMethod()
    {
        $request = new Request();
        $config = $this->createConfiguration(array(
            'method' => 'customClassMethod',
        ));

        $request->attributes->set('user', 'user');

        $this->setExpectedException('LogicException');
        $this->converter->apply($request, $config);
    }

    public function testDefaultValueInRepositoryMethod()
    {
        $request = new Request();
        $config = $this->createConfiguration(array(
            'method' => 'customDefaultMethod',
        ));

        $request->attributes->set('group', 'group');

        $entity = new \stdClass();

        $this->repository->expects($this->once())
            ->method('customDefaultMethod')
            ->with($this->equalTo('group'))
            ->will($this->returnValue($entity));

        $this->assertTrue($this->converter->apply($request, $config));
        $this->assertEquals($entity, $request->attributes->get(self::PARAMETER_NAME));
    }

    public function testApplyWithOptional()
    {
        $request = new Request();
        $config  = $this->createConfiguration();

        $request->attributes->set('id', 42);

        $config->expects($this->once())
            ->method('isOptional')
            ->will($this->returnValue(false));

        $this->repository->expects($this->once())
            ->method('find')
            ->with($this->equalTo(42))
            ->will($this->returnValue(null));

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $this->converter->apply($request, $config);
    }

}

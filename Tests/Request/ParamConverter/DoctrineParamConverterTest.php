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
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Doctrine\Common\Persistence\ManagerRegistry;

class DoctrineParamConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var DoctrineParamConverter
     */
    private $converter;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
            $this->markTestSkipped();
        }

        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->converter = new DoctrineParamConverter($this->registry);
    }

    public function createConfiguration($class = null, array $options = null, $name = 'arg', $isOptional = false)
    {
        $methods = array('getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray');
        if (null !== $isOptional) {
            $methods[] = 'isOptional';
        }
        $config = $this
            ->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
        if ($options !== null) {
            $config->expects($this->once())
                   ->method('getOptions')
                   ->will($this->returnValue($options));
        }
        if ($class !== null) {
            $config->expects($this->any())
                   ->method('getClass')
                   ->will($this->returnValue($class));
        }
        $config->expects($this->any())
               ->method('getName')
               ->will($this->returnValue($name));
        if (null !== $isOptional) {
            $config->expects($this->any())
                   ->method('isOptional')
                   ->will($this->returnValue($isOptional));
        }

        return $config;
    }

    public function testApplyWithNoIdAndData()
    {
        $request = new Request();
        $config = $this->createConfiguration(null, array());
        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->setExpectedException('LogicException');
        $this->converter->apply($request, $config);
    }

    public function testApplyWithNoIdAndDataOptional()
    {
        $request = new Request();
        $config = $this->createConfiguration(null, array(), 'arg', true);
        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertNull($request->attributes->get('arg'));
    }

    public function testApplyWithStripNulls()
    {
        $request = new Request();
        $request->attributes->set('arg', null);
        $config = $this->createConfiguration('stdClass', array('mapping' => array('arg' => 'arg'), 'strip_null' => true), 'arg', true);

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $manager->expects($this->once())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->will($this->returnValue($classMetadata));

        $manager->expects($this->never())
            ->method('getRepository');

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('stdClass')
            ->will($this->returnValue($manager));

        $classMetadata->expects($this->once())
            ->method('hasField')
            ->with($this->equalTo('arg'))
            ->will($this->returnValue(true));

        $this->converter->apply($request, $config);

        $this->assertNull($request->attributes->get('arg'));
    }

    /**
     * @dataProvider idsProvider
     */
    public function testApplyWithId($id)
    {
        $request = new Request();
        $request->attributes->set('id', $id);

        $config = $this->createConfiguration('stdClass', array('id' => 'id'), 'arg');

        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->registry->expects($this->once())
              ->method('getManagerForClass')
              ->with('stdClass')
              ->will($this->returnValue($manager));

        $manager->expects($this->once())
            ->method('getRepository')
            ->with('stdClass')
            ->will($this->returnValue($objectRepository));

        $objectRepository->expects($this->once())
                      ->method('find')
                      ->with($this->equalTo($id))
                      ->will($this->returnValue($object = new \stdClass()));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame($object, $request->attributes->get('arg'));
    }

    public function testUsedProperIdentifier()
    {
        $request = new Request();
        $request->attributes->set('id', 1);
        $request->attributes->set('entity_id', null);
        $request->attributes->set('arg', null);

        $config = $this->createConfiguration('stdClass', array('id' => 'entity_id'), 'arg', null);

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertNull($request->attributes->get('arg'));
    }

    public function idsProvider()
    {
        return array(
            array(1),
            array(0),
            array('foo'),
        );
    }

    public function testApplyGuessOptional()
    {
        $request = new Request();
        $request->attributes->set('arg', null);

        $config = $this->createConfiguration('stdClass', array(), 'arg', null);

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $manager->expects($this->once())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->will($this->returnValue($classMetadata));

        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->registry->expects($this->once())
              ->method('getManagerForClass')
              ->with('stdClass')
              ->will($this->returnValue($manager));

        $manager->expects($this->never())->method('getRepository');

        $objectRepository->expects($this->never())->method('find');
        $objectRepository->expects($this->never())->method('findOneBy');

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertNull($request->attributes->get('arg'));
    }

    public function testApplyWithMappingAndExclude()
    {
        $request = new Request();
        $request->attributes->set('foo', 1);
        $request->attributes->set('bar', 2);

        $config = $this->createConfiguration(
            'stdClass',
            array('mapping' => array('foo' => 'Foo'), 'exclude' => array('bar')),
            'arg'
        );

        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $this->registry->expects($this->once())
                ->method('getManagerForClass')
                ->with('stdClass')
                ->will($this->returnValue($manager));

        $manager->expects($this->once())
                ->method('getClassMetadata')
                ->with('stdClass')
                ->will($this->returnValue($metadata));
        $manager->expects($this->once())
                ->method('getRepository')
                ->with('stdClass')
                ->will($this->returnValue($repository));

        $metadata->expects($this->once())
                 ->method('hasField')
                 ->with($this->equalTo('Foo'))
                 ->will($this->returnValue(true));

        $repository->expects($this->once())
                      ->method('findOneBy')
                      ->with($this->equalTo(array('Foo' => 1)))
                      ->will($this->returnValue($object = new \stdClass()));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame($object, $request->attributes->get('arg'));
    }

    public function testApplyWithRepositoryMethod()
    {
        $request = new Request();
        $request->attributes->set('id', 1);

        $config = $this->createConfiguration(
            'stdClass',
            array('repository_method' => 'getClassName'),
            'arg'
        );

        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $manager->expects($this->once())
            ->method('getRepository')
            ->with('stdClass')
            ->will($this->returnValue($objectRepository));
        $this->registry->expects($this->once())
                      ->method('getManagerForClass')
                      ->will($this->returnValue($manager));

        $objectRepository->expects($this->once())
                      ->method('getClassName')
                      ->will($this->returnValue($className = 'ObjectRepository'));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame($className, $request->attributes->get('arg'));
    }

    public function testApplyWithRepositoryMethodAndMapping()
    {
        $request = new Request();
        $request->attributes->set('id', 1);

        $config = $this->createConfiguration(
            'stdClass',
            array('repository_method' => 'getClassName', 'mapping' => array('foo' => 'Foo')),
            'arg'
        );

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with('stdClass')
            ->will($this->returnValue($objectRepository));

        $this->registry->expects($this->once())
                    ->method('getManagerForClass')
                    ->will($this->returnValue($objectManager));

        $metadata->expects($this->once())
                 ->method('hasField')
                 ->with($this->equalTo('Foo'))
                 ->will($this->returnValue(true));

        $objectManager->expects($this->once())
                      ->method('getClassMetadata')
                      ->will($this->returnValue($metadata));
        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with('stdClass')
            ->will($this->returnValue($objectRepository));

        $objectRepository->expects($this->once())
                      ->method('getClassName')
                      ->will($this->returnValue($className = 'ObjectRepository'));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame($className, $request->attributes->get('arg'));
    }

    public function testApplyWithRepositoryMethodAndMapMethodSignature()
    {
        $request = new Request();
        $request->attributes->set('first_name', 'Fabien');
        $request->attributes->set('last_name', 'Potencier');

        $config = $this->createConfiguration(
            'stdClass',
            array(
                'repository_method' => 'findByFullName',
                'mapping' => array('first_name' => 'firstName', 'last_name' => 'lastName'),
                'map_method_signature' => true,
            ),
            'arg'
        );

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectRepository = new TestUserRepository();
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with('stdClass')
            ->will($this->returnValue($objectRepository));

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->will($this->returnValue($objectManager));

        $objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadata));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame('Fabien Potencier', $request->attributes->get('arg'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Repository method "Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter\TestUserRepository::findByFullName" requires that you provide a value for the "$lastName" argument.
     */
    public function testApplyWithRepositoryMethodAndMapMethodSignatureException()
    {
        $request = new Request();
        $request->attributes->set('first_name', 'Fabien');
        $request->attributes->set('last_name', 'Potencier');

        $config = $this->createConfiguration(
            'stdClass',
            array(
                'repository_method' => 'findByFullName',
                'mapping' => array('first_name' => 'firstName', 'last_name' => 'lastNameXxx'),
                'map_method_signature' => true,
            ),
            'arg'
        );

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectRepository = new TestUserRepository();
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with('stdClass')
            ->will($this->returnValue($objectRepository));

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->will($this->returnValue($objectManager));

        $objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->returnValue($metadata));

        $this->converter->apply($request, $config);
    }

    public function testSupports()
    {
        $config = $this->createConfiguration('stdClass', array());
        $metadataFactory = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');
        $metadataFactory->expects($this->once())
                        ->method('isTransient')
                        ->with($this->equalTo('stdClass'))
                        ->will($this->returnValue(false));

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->once())
                      ->method('getMetadataFactory')
                      ->will($this->returnValue($metadataFactory));

        $this->registry->expects($this->once())
                    ->method('getManagers')
                    ->will($this->returnValue(array($objectManager)));

        $this->registry->expects($this->once())
                      ->method('getManagerForClass')
                      ->with('stdClass')
                      ->will($this->returnValue($objectManager));

        $ret = $this->converter->supports($config);

        $this->assertTrue($ret, 'Should be supported');
    }

    public function testSupportsWithConfiguredEntityManager()
    {
        $config = $this->createConfiguration('stdClass', array('entity_manager' => 'foo'));
        $metadataFactory = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');
        $metadataFactory->expects($this->once())
                        ->method('isTransient')
                        ->with($this->equalTo('stdClass'))
                        ->will($this->returnValue(false));

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->once())
                      ->method('getMetadataFactory')
                      ->will($this->returnValue($metadataFactory));

        $this->registry->expects($this->once())
                    ->method('getManagers')
                    ->will($this->returnValue(array($objectManager)));

        $this->registry->expects($this->once())
                      ->method('getManager')
                      ->with('foo')
                      ->will($this->returnValue($objectManager));

        $ret = $this->converter->supports($config);

        $this->assertTrue($ret, 'Should be supported');
    }
}

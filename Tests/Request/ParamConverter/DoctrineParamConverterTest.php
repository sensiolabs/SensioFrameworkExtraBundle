<?php

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
                      ->will($this->returnValue($object =new \stdClass));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame($object, $request->attributes->get('arg'));
    }

    /**
     * @dataProvider idsProvider
     */
    public function testApplyWithIdInQuery($id)
    {
        $request = new Request();
        $request->query->set('id', $id);

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
            ->will($this->returnValue($object =new \stdClass));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame($object, $request->attributes->get('arg'));
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
        $request->query->set('baz', 3);
        $request->query->set('qux', 4);

        $config = $this->createConfiguration(
            'stdClass',
            array('mapping' => array('foo' => 'Foo', 'baz' => 'Baz'), 'exclude' => array('bar', 'qux')),
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

        $metadata->expects($this->exactly(2))
                 ->method('hasField')
                 ->with($this->logicalOr(
                    $this->equalTo('Foo'),
                    $this->equalTo('Baz')
                 ))
                 ->will($this->returnValue(true));

        $repository->expects($this->once())
                      ->method('findOneBy')
                      ->with($this->equalTo(array('Foo' => 1, 'Baz' => 3)))
                      ->will($this->returnValue($object =new \stdClass));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame($object, $request->attributes->get('arg'));
    }

    public function testApplyWithDateTimeInMapping()
    {
        $request = new Request();
        $dateTimeText1 = '2014-06-11';
        $dateTimeText2 = '2011-12-20';
        $expected1 = new \DateTime($dateTimeText1);
        $expected2 = new \DateTime($dateTimeText2);
        $request->attributes->set('foo', $dateTimeText1);
        $request->query->set('bar', $dateTimeText2);

        $config = $this->createConfiguration(
            'stdClass',
            array('mapping' => array('foo' => 'Foo', 'bar' => 'Bar')),
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

        $metadata->expects($this->exactly(2))
            ->method('hasField')
            ->with($this->logicalOr(
                $this->equalTo('Foo'),
                $this->equalTo('Bar')
            ))
            ->will($this->returnValue(true));

        $metadata->expects($this->exactly(2))
            ->method('getTypeOfField')
            ->with($this->logicalOr(
                $this->equalTo('Foo'),
                $this->equalTo('Bar')
            ))
            ->will($this->returnValue('datetime'));

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('Foo' => $expected1, 'Bar' => $expected2)))
            ->will($this->returnValue($object =new \stdClass));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame($object, $request->attributes->get('arg'));
    }

    public function testApplyWithInvalidDateTimeInMapping()
    {
        $request = new Request();
        $dateTimeText = 'Not a valid date';
        $request->attributes->set('foo', $dateTimeText);

        $config = $this->createConfiguration(
            'stdClass',
            array('mapping' => array('foo' => 'Foo')),
            'arg'
        );

        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('stdClass')
            ->will($this->returnValue($manager));

        $manager->expects($this->once())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->will($this->returnValue($metadata));

        $metadata->expects($this->once())
            ->method('hasField')
            ->with($this->logicalOr(
                $this->equalTo('Foo'),
                $this->equalTo('Bar')
            ))
            ->will($this->returnValue(true));

        $metadata->expects($this->once())
            ->method('getTypeOfField')
            ->with($this->equalTo('Foo'))
            ->will($this->returnValue('datetime'));

        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->converter->apply($request, $config);
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

    public function testSupports()
    {
        $config = $this->createConfiguration('stdClass', array());
        $metadataFactory = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');
        $metadataFactory->expects($this->once())
                        ->method('isTransient')
                        ->with($this->equalTo('stdClass'))
                        ->will($this->returnValue( false ));

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

        $this->assertTrue($ret, "Should be supported");
    }

    public function testSupportsWithConfiguredEntityManager()
    {
        $config = $this->createConfiguration('stdClass', array('entity_manager' => 'foo'));
        $metadataFactory = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');
        $metadataFactory->expects($this->once())
                        ->method('isTransient')
                        ->with($this->equalTo('stdClass'))
                        ->will($this->returnValue( false ));

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

        $this->assertTrue($ret, "Should be supported");
    }
}

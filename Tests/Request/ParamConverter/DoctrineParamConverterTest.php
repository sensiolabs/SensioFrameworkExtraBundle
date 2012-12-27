<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;

class DoctrineParamConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Doctrine\Common\Persistence\ManagerRegistry
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
        $config = $this->getMock(
            'Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface', array(
            'getClass', 'getAliasName', 'getOptions', 'isOptional', 'getName',
        ));
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
        $config->expects($this->any())
               ->method('isOptional')
               ->will($this->returnValue($isOptional));

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

    public function idsProvider()
    {
        return array(
            array(1),
            array(0),
            array('foo'),
        );
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
                      ->will($this->returnValue($object =new \stdClass));

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret);
        $this->assertSame($object, $request->attributes->get('arg'));
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

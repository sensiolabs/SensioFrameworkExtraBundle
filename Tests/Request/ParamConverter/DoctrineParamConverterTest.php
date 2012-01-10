<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;

class DoctrineParamConverterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Doctrine\Common\Persistence\ManagerRegistry
     */
    private $manager;

    /**
     * @var DoctrineParamConverter
     */
    private $converter;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
            $this->markTestSkipped('Missing Doctrine\Common\Persistence\ManagerRegistry');
        }

        $this->manager = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->converter = new DoctrineParamConverter($this->manager);

        $this->objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->manager->expects($this->any())->method('getRepository')
                ->will($this->returnValue($this->objectRepository));
        $this->manager->expects($this->any())->method('getManager')
                ->will($this->returnValue($this->objectManager));


        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->any())
                ->method('hasField')
                ->will($this->returnValue(false));

        $this->objectManager->expects($this->any())
                ->method('getClassMetadata')
                ->will($this->returnValue($classMetadata));
    }


    public function createConfiguration($class = null, array $options = null)
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
                ->method("getName")
                ->will($this->returnValue("doc"));
        return $config;
    }


    public function testApplyWithNoIdAndData()
    {
        $request = new Request();
        $config = $this->createConfiguration(null, array());

        $this->setExpectedException('LogicException');
        $this->converter->apply($request, $config);
    }


    public function testNonOptionalNotFound()
    {
        $request = new Request();
        $config = $this->createConfiguration(null, array());
        $config->expects($this->once())
                ->method("isOptional")
                ->will($this->returnValue(false));

        $request->attributes->set("doc", "34");

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $this->converter->apply($request, $config);
    }


    /**
     * Old behaviour, we use the id attribute to query for the user by id
     */
    public function testApplyDefault()
    {
        $request = new Request();
        $config = $this->createConfiguration("User", array());

        $request->attributes->set("doc", "testName");

        $this->objectRepository->expects($this->once())->method('find')
                ->with($this->equalTo("testName"))->will($this->returnValue("object"))
        ;
        $this->objectRepository->expects($this->never())->method('findBy');

        $ret = $this->converter->apply($request, $config);

        $this->assertTrue($ret, "We should have found an object");
        $this->assertEquals("object", $request->attributes->get($config->getName()));
    }


    
    public function testSupports()
    {
        $config = $this->createConfiguration('stdClass', array());
        $metadataFactory = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');
        $metadataFactory->expects($this->once())
                ->method('isTransient')
                ->with($this->equalTo('stdClass'))
                ->will($this->returnValue(false));

        $this->objectManager->expects($this->once())
                ->method('getMetadataFactory')
                ->will($this->returnValue($metadataFactory));

        $ret = $this->converter->supports($config);



        $this->assertTrue($ret, "Should be supported");
    }


}

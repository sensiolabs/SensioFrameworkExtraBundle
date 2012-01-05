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
<<<<<<< HEAD
        if (!interface_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
            $this->markTestSkipped();
=======
            if (!interface_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
            $this->markTestSkipped('Missing Doctrine\Common\Persistence\ManagerRegistry');
>>>>>>> 62cf316... This commit changes the DoctrineParamConverter to handle paths like:
        }

        $this->manager = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->converter = new DoctrineParamConverter($this->manager);
<<<<<<< HEAD
=======
        
        $this->objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->manager->expects($this->any())->method('getRepository')
                      ->will($this->returnValue($this->objectRepository));
        $this->manager->expects($this->any())->method('getManager')
                      ->will($this->returnValue($this->objectManager));
        
>>>>>>> 62cf316... This commit changes the DoctrineParamConverter to handle paths like:
    }
    
    public function createConfiguration($class = null, array $options = null)
    {
        $config = $this->getMock(
            'Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface', array(
<<<<<<< HEAD
            'getClass', 'getAliasName', 'getOptions'
=======
            'getClass', 'getAliasName', 'getOptions', 'isOptional', 'getName', 
>>>>>>> 62cf316... This commit changes the DoctrineParamConverter to handle paths like:
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
        return $config;
    }
    
    public function testApplyWithNoIdAndData()
    {
        $request = new Request();
        $config = $this->createConfiguration(null, array());
<<<<<<< HEAD
        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        
        $this->manager->expects($this->never())->method('find');
        $this->manager->expects($this->once())
                      ->method('getManager')
                      ->will($this->returnValue($objectManager));
        
        $this->setExpectedException('LogicException');
        $this->converter->apply($request, $config);
    }
    
=======
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
                
        $request->attributes->set("id", "34");
        
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
        
        $request->attributes->set("id", "testName");
        
        $this->objectRepository->expects($this->once())->method('find')
                ->with($this->equalTo("testName"))->will($this->returnValue("object"))
                ;
        $this->objectRepository->expects($this->never())->method('findBy');
        
        $ret = $this->converter->apply($request, $config);
        
        $this->assertTrue($ret, "We should have found an object");
        $this->assertEquals("object", $request->attributes->get($config->getName()));
    }

    public function testApplyWithRequestAttribute()
    {
        $request = new Request();
        $config = $this->createConfiguration("User", array('request_attribute' => 'name'));
        
        $request->attributes->set("name", "testName");
        
        $this->objectRepository->expects($this->once())->method('find')
                ->with($this->equalTo("testName"))->will($this->returnValue("object"))
                ;
        $this->objectRepository->expects($this->never())->method('findBy');
        
        $ret = $this->converter->apply($request, $config);
        
        $this->assertTrue($ret, "We should have found an object");
        $this->assertEquals("object", $request->attributes->get($config->getName()));
    }

        public function testApplyWithRequestAttributeAndQueryAttribute()
    {
        $request = new Request();
        $config = $this->createConfiguration("User", array('request_attribute' => 'name'));
        
        $request->attributes->set("name", "testName");
        
        $this->objectRepository->expects($this->once())->method('find')
                ->with($this->equalTo("testName"))->will($this->returnValue("object"))
                ;
        $this->objectRepository->expects($this->never())->method('findBy');
        
        $ret = $this->converter->apply($request, $config);
        
        $this->assertTrue($ret, "We should have found an object");
        $this->assertEquals("object", $request->attributes->get($config->getName()));
    }

>>>>>>> 62cf316... This commit changes the DoctrineParamConverter to handle paths like:
    public function testSupports()
    {
        $config = $this->createConfiguration('stdClass', array());
        $metadataFactory = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');
        $metadataFactory->expects($this->once())
                        ->method('isTransient')
                        ->with($this->equalTo('stdClass'))
                        ->will($this->returnValue( false ));
        
<<<<<<< HEAD
        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->once())
=======
        $this->objectManager->expects($this->once())
>>>>>>> 62cf316... This commit changes the DoctrineParamConverter to handle paths like:
                      ->method('getMetadataFactory')
                      ->will($this->returnValue($metadataFactory));
        
        $this->manager->expects($this->once())
                      ->method('getManager')
                      ->with($this->equalTo('default'))
<<<<<<< HEAD
                      ->will($this->returnValue($objectManager));
        
        $ret = $this->converter->supports($config);
        
        $this->assertTrue($ret, "Should be supported");
=======
                      ->will($this->returnValue($this->objectManager));
        
        $ret = $this->converter->supports($config);
        
        
        
        $this->assertTrue($ret, "Should be supported");
        
>>>>>>> 62cf316... This commit changes the DoctrineParamConverter to handle paths like:
    }
}

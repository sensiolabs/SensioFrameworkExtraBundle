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
            $this->markTestSkipped();
        }

        $this->manager = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->converter = new DoctrineParamConverter($this->manager);
    }
    
    public function createConfiguration($class = null, array $options = null)
    {
        $config = $this->getMock(
            'Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface', array(
            'getClass', 'getAliasName', 'getOptions'
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
        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        
        $this->manager->expects($this->never())->method('find');
        $this->manager->expects($this->once())
                      ->method('getManager')
                      ->will($this->returnValue($objectManager));
        
        $this->setExpectedException('LogicException');
        $this->converter->apply($request, $config);
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
        
        $this->manager->expects($this->once())
                      ->method('getManager')
                      ->with($this->equalTo('default'))
                      ->will($this->returnValue($objectManager));
        
        $ret = $this->converter->supports($config);
        
        $this->assertTrue($ret, "Should be supported");
    }
}

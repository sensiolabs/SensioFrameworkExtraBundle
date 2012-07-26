<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Symfony\Component\HttpFoundation\Request;

class ParamConverterManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->manager = new ParamConverterManager();
    }

    public function testPriorities()
    {
        $this->assertEquals(array(), $this->manager->all());

        $high = $this->createParamConverterMock();
        $low = $this->createParamConverterMock();

        $this->manager->add($low);
        $this->manager->add($high, 10);

        $this->assertEquals(array(
            $high,
            $low,
        ), $this->manager->all());
    }

    public function testApply()
    {
        $supported = $this->createParamConverterMock();
        $supported
            ->expects($this->once())
            ->method('supports')
            ->will($this->returnValue(true))
        ;
        $supported
            ->expects($this->once())
            ->method('apply')
            ->will($this->returnValue(false))
        ;

        $invalid = $this->createParamConverterMock();
        $invalid
            ->expects($this->once())
            ->method('supports')
            ->will($this->returnValue(false))
        ;
        $invalid
            ->expects($this->never())
            ->method('apply')
        ;

        $configurations = array(
            new Configuration\ParamConverter(array(
                'name' => 'var',
            )),
        );

        $this->manager->add($supported);
        $this->manager->add($invalid);
        $this->manager->apply(new Request(), $configurations);
    }

    public function testApplyNamedConverter()
    {
        $converter = $this->createParamConverterMock();
        $converter
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(True))
        ;

        $converter
            ->expects($this->any())
            ->method('apply')
        ;

        $this->manager->add($converter, 0, "test");

        $request = new Request();
        $request->attributes->set('param', '1234');

        $configuration = new Configuration\ParamConverter(array(
            'name' => 'param',
            'class' => 'stdClass',
            'converter' => 'test',
        ));

        $this->manager->apply($request, array($configuration));
    }

    public function testApplyNamedConverterNotSupportsParameter()
    {
        $converter = $this->createParamConverterMock();
        $converter
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(false))
        ;

        $this->manager->add($converter, 0, "test");

        $request = new Request();
        $request->attributes->set('param', '1234');

        $configuration = new Configuration\ParamConverter(array(
            'name' => 'param',
            'class' => 'stdClass',
            'converter' => 'test',
        ));

        $this->setExpectedException("RuntimeException", "Converter 'test' does not support conversion of parameter 'param'.");
        $this->manager->apply($request, array($configuration));
    }

    public function testApplyNamedConverterNoConverter()
    {
        $request = new Request();
        $request->attributes->set('param', '1234');

        $configuration = new Configuration\ParamConverter(array(
            'name' => 'param',
            'class' => 'stdClass',
            'converter' => 'test',
        ));

        $this->setExpectedException("RuntimeException", "No converter named 'test' found for conversion of parameter 'param'.");
        $this->manager->apply($request, array($configuration));
    }

    public function testApplyNotCalledOnAlreadyConvertedObjects()
    {

        $converter = $this->createParamConverterMock();
        $converter
            ->expects($this->never())
            ->method('supports')
        ;

        $converter
            ->expects($this->never())
            ->method('apply')
        ;

        $this->manager->add($converter);

        $request = new Request();
        $request->attributes->set('converted', new \stdClass);

        $configuration = new Configuration\ParamConverter(array(
            'name' => 'converted',
            'class' => 'stdClass',
        ));

        $this->manager->apply($request, array($configuration));
    }

    protected function createParamConverterMock()
    {
        return $this->getMock('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface');
    }
}

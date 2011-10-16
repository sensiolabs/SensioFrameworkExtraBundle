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

    protected function createParamConverterMock()
    {
        return $this->getMock('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface');
    }
}

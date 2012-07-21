<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ObjectParamConverter;

class ObjectParamConverterTest extends \PHPUnit_Framework_TestCase
{
    private $converter;

    public function setUp()
    {
        $this->converter = new ObjectParamConverter();
    }

    public function testApply()
    {
        $request = Request::create('/', 'POST');
        $request->query->set('foo', array('bar' => array('foo' => 1), 'baz' => '2012-07-21'));

        $config  = $this->createConfiguration('foo', __NAMESPACE__ . '\\Foo');

        $this->converter->apply($request, $config);

        $foo = $request->attributes->get('foo');
        $this->assertInstanceOf(__NAMESPACE__ . '\\Foo', $foo);
        $this->assertInstanceOf(__NAMESPACE__ . '\\Bar', $foo->bar);
        $this->assertInstanceOf('DateTime', $foo->baz);
        $this->assertEquals(1, $foo->bar->foo);
    }

    public function createConfiguration($name, $class, array $options = null)
    {
        $config = $this->getMock(
            'Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface', array(
            'getClass', 'getAliasName', 'getOptions', 'getName'
        ));
        if ($options !== null) {
            $config->expects($this->once())
                   ->method('getOptions')
                   ->will($this->returnValue($options));
        }
        $config->expects($this->any())
               ->method('getClass')
               ->will($this->returnValue($class));
        $config->expects($this->any())
               ->method('getName')
               ->will($this->returnValue($name));

        return $config;
    }
}

class Foo
{
    public $bar;
    public $baz;

    public function __construct(Bar $bar, \DateTime $baz)
    {
        $this->bar = $bar;
        $this->baz = $baz;
    }
}

class Bar
{
    public $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }
}

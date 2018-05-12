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

use PHPUnit\Framework\MockObject\MockObject;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\HashidsParamConverter;
use Symfony\Component\HttpFoundation\Request;

class HashidsParamConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject
     */
    private $innerConverter;

    /**
     * @var MockObject
     */
    private $hashids;

    /**
     * @var HashidsParamConverter
     */
    private $converter;

    public function setUp()
    {
        $this->innerConverter = $this->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface')->getMock();
        $this->hashids = $this
            ->getMockBuilder('Hashids\Hashids')
            ->setMethods(['encode', 'decode'])
            ->getMock();
        $this->converter = new HashidsParamConverter($this->innerConverter, $this->hashids);
    }

    public function testSupports()
    {
        $supportedConfig = $this->createConfiguration();
        $unsupportedConfig = $this->createConfiguration('DateTime');

        $this->innerConverter
            ->expects($this->at(0))
            ->method('supports')
            ->with($supportedConfig)
            ->willReturn(true);

        $this->innerConverter
            ->expects($this->at(1))
            ->method('supports')
            ->with($unsupportedConfig)
            ->willReturn(false);

        $this->assertTrue($this->converter->supports($supportedConfig));
        $this->assertFalse($this->converter->supports($unsupportedConfig));
    }

    /**
     * Test apply with id attribute.
     */
    public function testApplyDefaultID()
    {
        $request = new Request([], [], ['id' => 'olejRejN', 'user' => 2, 'slug' => 'test', 'book' => 'WPe9xdLy']);
        $config = $this->createConfiguration(null, 'id');

        $this->hashids
            ->expects($this->once())
            ->method('decode')
            ->with($this->equalTo('olejRejN'))
            ->willReturn([1]);

        $this->innerConverter
            ->expects($this->once())
            ->method('apply')
            ->with($this->equalTo($request), $this->equalTo($config));

        $this->converter->apply($request, $config);

        $this->assertEquals(1, $request->attributes->get('id'), "ID hasn't been properly decoded.");
        $this->assertEquals(2, $request->attributes->get('user'));
        $this->assertEquals('test', $request->attributes->get('slug'));
        $this->assertEquals('WPe9xdLy', $request->attributes->get('book'), 'Book attribute has been decoded but was not requested to be decoded.');
    }

    /**
     * Test apply with single named attribute.
     */
    public function testApplySingleNamedID()
    {
        $request = new Request([], [], ['post' => 'olejRejN', 'user' => 2, 'slug' => 'test', 'book' => 'WPe9xdLy']);
        $config = $this->createConfiguration(null, 'post');

        $this->hashids
            ->expects($this->once())
            ->method('decode')
            ->with($this->equalTo('olejRejN'))
            ->willReturn([1]);

        $this->innerConverter
            ->expects($this->once())
            ->method('apply')
            ->with($this->equalTo($request), $this->equalTo($config));

        $this->converter->apply($request, $config);

        $this->assertEquals(1, $request->attributes->get('post'));
        $this->assertEquals(2, $request->attributes->get('user'));
        $this->assertEquals('test', $request->attributes->get('slug'));
        $this->assertEquals('WPe9xdLy', $request->attributes->get('book'), 'Book attribute has been decoded but was not requested to be decoded.');
    }

    /**
     * Test apply with multiple attributes.
     */
    public function testApplyMultipleNamedIDs()
    {
        $id = ['olejRejN', 'pmbk5ezJ'];
        $request = new Request([], [], ['post' => $id[0], 'user' => $id[1], 'slug' => 'test', 'book' => 'WPe9xdLy']);
        $config = $this->createConfiguration(null, ['post', 'user']);

        $this->hashids
            ->expects($this->at(0))
            ->method('decode')
            ->with($this->equalTo($id[0]))
            ->willReturn([1]);

        $this->hashids
            ->expects($this->at(1))
            ->method('decode')
            ->with($this->equalTo($id[1]))
            ->willReturn([2]);

        $this->innerConverter
            ->expects($this->once())
            ->method('apply')
            ->with($this->equalTo($request), $this->equalTo($config));

        $this->converter->apply($request, $config);

        $this->assertEquals(1, $request->attributes->get('post'));
        $this->assertEquals(2, $request->attributes->get('user'));
        $this->assertEquals('test', $request->attributes->get('slug'));
        $this->assertEquals('WPe9xdLy', $request->attributes->get('book'), 'Book attribute has been decoded but was not requested to be decoded.');
    }

    /**
     * Test apply fallback to id attribute for non-existing foo attribute.
     */
    public function testApplyNonExistingNamedIDWithDefault()
    {
        $request = new Request([], [], ['id' => 'olejRejN', 'user' => 2, 'slug' => 'test', 'book' => 'WPe9xdLy']);
        $config = $this->createConfiguration(null, 'foo');

        $this->hashids
            ->expects($this->once())
            ->method('decode')
            ->with($this->equalTo('olejRejN'))
            ->willReturn([1]);

        $this->innerConverter
            ->expects($this->once())
            ->method('apply')
            ->with($this->equalTo($request), $this->equalTo($config));

        $this->converter->apply($request, $config);

        $this->assertEquals(1, $request->attributes->get('id'), "ID hasn't been properly decoded.");
        $this->assertEquals(2, $request->attributes->get('user'));
        $this->assertEquals('test', $request->attributes->get('slug'));
        $this->assertEquals('WPe9xdLy', $request->attributes->get('book'), 'Book attribute has been decoded but was not requested to be decoded.');
    }

    /**
     * Test apply with non-existing attribute and no id attribute to fall back to.
     */
    public function testApplyNonExistingNamedIDWithoutDefault()
    {
        $request = new Request([], [], ['user' => 2, 'slug' => 'test', 'book' => 'WPe9xdLy']);
        $config = $this->createConfiguration(null, 'foo');

        $this->hashids
            ->expects($this->never())
            ->method('decode');

        $this->innerConverter
            ->expects($this->once())
            ->method('apply')
            ->with($this->equalTo($request), $this->equalTo($config));

        $this->converter->apply($request, $config);

        $this->assertEquals(2, $request->attributes->get('user'));
        $this->assertEquals('test', $request->attributes->get('slug'));
        $this->assertEquals('WPe9xdLy', $request->attributes->get('book'), 'Book attribute has been decoded but was not requested to be decoded.');
    }

    /**
     * Create configuration mock.
     *
     * @param string|null $class
     * @param string|null $name
     *
     * @return MockObject
     */
    protected function createConfiguration($class = null, $name = null)
    {
        $config = $this
            ->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
            ->setMethods(['getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray', 'isOptional'])
            ->disableOriginalConstructor()
            ->getMock();

        if (null !== $name) {
            $config->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue($name));
        }
        if (null !== $class) {
            $config->expects($this->any())
                   ->method('getClass')
                   ->will($this->returnValue($class));
        }

        return $config;
    }
}

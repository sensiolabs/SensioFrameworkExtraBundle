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

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\EnumParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Tests\Fixtures\FooEnum;

class EnumParamConverterTest extends \PHPUnit\Framework\TestCase
{
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new EnumParamConverter();
    }

    public function testSupports()
    {
        $config = $this->createConfiguration('Tests\\Fixtures\\FooEnum');
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($this->converter->supports($config));

        $config = $this->createConfiguration();
        $this->assertFalse($this->converter->supports($config));
    }

    public function testApply()
    {
        $request = new Request([], [], ['enum' => 'foo']);
        $config = $this->createConfiguration('Tests\\Fixtures\\FooEnum', 'enum');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf('Tests\\Fixtures\\FooEnum', $request->attributes->get('enum'));
        $this->assertEquals(FooEnum::foo, $request->attributes->get('enum'));
    }

    public function createConfiguration($class = null, $name = null)
    {
        $config = $this
            ->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
            ->setMethods(['getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray', 'isOptional'])
            ->disableOriginalConstructor()
            ->getMock();

        if (null !== $name) {
            $config->expects($this->any())
                   ->method('getName')
                   ->willReturn($name);
        }
        if (null !== $class) {
            $config->expects($this->any())
                   ->method('getClass')
                   ->willReturn($class);
        }

        return $config;
    }
}

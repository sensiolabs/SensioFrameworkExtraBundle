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

use Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateIntervalParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Tests\Fixtures;

class DateIntervalParamConverterTest extends \PHPUnit\Framework\TestCase
{
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new DateIntervalParamConverter();
    }

    public function testSupports()
    {
        $config = $this->createConfiguration(\DateInterval::class);
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(Fixtures\FooDateInterval::class);
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($this->converter->supports($config));

        $config = $this->createConfiguration();
        $this->assertFalse($this->converter->supports($config));
    }

    public function testApplyIso8601()
    {
        $request = new Request([], [], ['start' => 'P1DT3H30M']);
        $config = $this->createConfiguration(\DateInterval::class, 'start');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf(\DateInterval::class, $request->attributes->get('start'));
        $this->assertEquals('1 day, 3 hours, 30 minutes', $request->attributes->get('start')->format('%d day, %h hours, %i minutes'));
    }

    public function testApplyDateString()
    {
        $request = new Request([], [], ['start' => '2 weeks']);
        $config = $this->createConfiguration(\DateInterval::class, 'start');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf(\DateInterval::class, $request->attributes->get('start'));
        $this->assertEquals('14', $request->attributes->get('start')->format('%d'));
    }

    public function testApplyInvalidDateInterval404Exception()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid date interval given for parameter "start".');

        $request = new Request([], [], ['start' => 'invalid date interval']);
        $config = $this->createConfiguration(\DateInterval::class, 'start');

        $this->converter->apply($request, $config);
    }

    public function testApplyOptionalWithEmptyAttribute()
    {
        $request = new Request([], [], ['start' => '']);
        $config = $this->createConfiguration(\DateInterval::class, 'start');
        $config->expects($this->once())
            ->method('isOptional')
            ->willReturn(true);

        $this->assertTrue($this->converter->apply($request, $config));
        $this->assertNull($request->attributes->get('start'));
    }

    public function testApplyCustomClass()
    {
        $request = new Request([], [], ['start' => 'P1DT3H30M']);
        $config = $this->createConfiguration(Fixtures\FooDateInterval::class, 'start');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf(Fixtures\FooDateInterval::class, $request->attributes->get('start'));
        $this->assertEquals('1 day, 3 hours, 30 minutes', $request->attributes->get('start')->format('%d day, %h hours, %i minutes'));
    }

    public function createConfiguration($class = null, $name = null)
    {
        $config = $this
            ->getMockBuilder(Configuration\ParamConverter::class)
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

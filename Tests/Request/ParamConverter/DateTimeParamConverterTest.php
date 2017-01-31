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

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter;

class DateTimeParamConverterTest extends \PHPUnit_Framework_TestCase
{
    private $converter;

    public function setUp()
    {
        $this->converter = new DateTimeParamConverter();
    }

    public function testSupports()
    {
        $config = $this->createConfiguration('DateTime');
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($this->converter->supports($config));

        $config = $this->createConfiguration();
        $this->assertFalse($this->converter->supports($config));
    }

    public function testApply()
    {
        $request = new Request(array(), array(), array('start' => '2012-07-21 00:00:00'));
        $config = $this->createConfiguration('DateTime', 'start');

        $this->converter->apply($request, $config);

        $this->assertInstanceOf('DateTime', $request->attributes->get('start'));
        $this->assertEquals('2012-07-21', $request->attributes->get('start')->format('Y-m-d'));
    }

    public function testApplyInvalidDate404Exception()
    {
        $request = new Request(array(), array(), array('start' => 'Invalid DateTime Format'));
        $config = $this->createConfiguration('DateTime', 'start');

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'Invalid date given for parameter "start".');
        $this->converter->apply($request, $config);
    }

    public function testApplyWithFormatInvalidDate404Exception()
    {
        $request = new Request(array(), array(), array('start' => '2012-07-21'));
        $config = $this->createConfiguration('DateTime', 'start');
        $config->expects($this->any())->method('getOptions')->will($this->returnValue(array('format' => 'd.m.Y')));

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', 'Invalid date given for parameter "start".');
        $this->converter->apply($request, $config);
    }

    public function testApplyOptionalWithEmptyAttribute()
    {
        $request = new Request(array(), array(), array('start' => null));
        $config = $this->createConfiguration('DateTime', 'start');
        $config->expects($this->once())
            ->method('isOptional')
            ->will($this->returnValue(true));

        $this->assertFalse($this->converter->apply($request, $config));
        $this->assertNull($request->attributes->get('start'));
    }

    public function createConfiguration($class = null, $name = null)
    {
        $config = $this
            ->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
            ->setMethods(array('getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray', 'isOptional'))
            ->disableOriginalConstructor()
            ->getMock();

        if ($name !== null) {
            $config->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue($name));
        }
        if ($class !== null) {
            $config->expects($this->any())
                   ->method('getClass')
                   ->will($this->returnValue($class));
        }

        return $config;
    }
}

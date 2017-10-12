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

use Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ArgumentNameConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getArgumentTests
     */
    public function testGetControllerArguments(array $resolvedArguments, array $argumentMetadatas, array $requestAttributes, array $expectedArguments)
    {
        $metadataFactory = $this->getMockBuilder(ArgumentMetadataFactoryInterface::class)->getMock();
        $metadataFactory->expects($this->any())
            ->method('createArgumentMetadata')
            ->will($this->returnValue($argumentMetadatas));

        $request = new Request();
        $request->attributes->add($requestAttributes);

        $converter = new ArgumentNameConverter($metadataFactory);
        $event = new FilterControllerArgumentsEvent($this->getMockBuilder(HttpKernelInterface::class)->getMock(), function () { return new Response(); }, $resolvedArguments, $request, null);
        $actualArguments = $converter->getControllerArguments($event);
        $this->assertSame($expectedArguments, $actualArguments);
    }

    public function getArgumentTests()
    {
        // everything empty
        yield array(array(), array(), array(), array());

        // uses request attributes
        yield array(array(), array(), array('post' => 5), array('post' => 5));

        // resolves argument names correctly
        $arg1Metadata = new ArgumentMetadata('arg1Name', 'string', false, false, null);
        $arg2Metadata = new ArgumentMetadata('arg2Name', 'string', false, false, null);
        yield array(array('arg1Value', 'arg2Value'), array($arg1Metadata, $arg2Metadata), array('post' => 5), array('post' => 5, 'arg1Name' => 'arg1Value', 'arg2Name' => 'arg2Value'));

        // argument names have priority over request attributes
        yield array(array('arg1Value', 'arg2Value'), array($arg1Metadata, $arg2Metadata), array('arg1Name' => 'differentValue'), array('arg1Name' => 'arg1Value', 'arg2Name' => 'arg2Value'));

        // variadic arguments are resolved correctly
        $arg1Metadata = new ArgumentMetadata('arg1Name', 'string', false, false, null);
        $arg2VariadicMetadata = new ArgumentMetadata('arg2Name', 'string', true, false, null);
        yield array(array('arg1Value', 'arg2Value', 'arg3Value'), array($arg1Metadata, $arg2VariadicMetadata), array(), array('arg1Name' => 'arg1Value', 'arg2Name' => array('arg2Value', 'arg3Value')));

        // variadic argument receives no arguments, so becomes an empty array
        yield array(array('arg1Value'), array($arg1Metadata, $arg2VariadicMetadata), array(), array('arg1Name' => 'arg1Value', 'arg2Name' => array()));

        // resolves nullable argument correctly
        $arg1Metadata = new ArgumentMetadata('arg1Name', 'string', false, false, null);
        $arg2NullableMetadata = new ArgumentMetadata('arg2Name', 'string', false, false, true);
        yield array(array('arg1Value', null), array($arg1Metadata, $arg2Metadata), array('post' => 5), array('post' => 5, 'arg1Name' => 'arg1Value', 'arg2Name' => null));
    }
}

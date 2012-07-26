<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ParamConverterListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestIsSkipped()
    {
        $kernel  = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $manager  = $this->getMock('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager');
        $manager->expects($this->once())
                ->method('apply')
                ->with($this->equalTo($request), $this->equalTo(array()));

        $listener = new ParamConverterListener($manager);
        $event    = new FilterControllerEvent($kernel, array(new TestController(), 'execute'), $request, null);

        $listener->onKernelController($event);
    }
}

class TestController
{
    public function execute(Request $request) {}
}


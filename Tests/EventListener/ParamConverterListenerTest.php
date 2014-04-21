<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $listener = new ParamConverterListener($this->getParamConverterManager($request, array()));
        $event = new FilterControllerEvent($kernel, array(new TestController(), 'noArgAction'), $request, null);

        $listener->onKernelController($event);
    }

    public function testAutoConvert()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request(array(), array(), array('date' => '2014-03-14 09:00:00'));

        $converter = new ParamConverter(array('name' => 'date', 'class' => 'DateTime'));

        $listener = new ParamConverterListener($this->getParamConverterManager($request, array('date' => $converter)));
        $event = new FilterControllerEvent($kernel, array(new TestController(), 'dateAction'), $request, null);

        $listener->onKernelController($event);
    }


    public function testNoAutoConvert()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request(array(), array(), array('date' => '2014-03-14 09:00:00'));

        $listener = new ParamConverterListener($this->getParamConverterManager($request, array()), false);
        $event = new FilterControllerEvent($kernel, array(new TestController(), 'dateAction'), $request, null);

        $listener->onKernelController($event);
    }

    protected function getParamConverterManager(Request $request, $configurations)
    {
        $manager = $this->getMock('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager');
        $manager
            ->expects($this->once())
            ->method('apply')
            ->with($this->equalTo($request), $this->equalTo($configurations))
        ;

        return $manager;
    }
}

class TestController
{
    public function noArgAction(Request $request)
    {
    }

    public function dateAction(\DateTime $date)
    {
    }
}

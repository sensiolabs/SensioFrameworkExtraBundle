<?php

namespace Tests\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TemplateListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelViewCanNotFindOwner()
    {
        $templating = $this->getTemplatingMock();
        $templating->expects($this->once())
            ->method('renderResponse')
            ->willReturn(new Response('bar'));
        $container = $this->getContainerMock();
        $container->expects($this->any())
            ->method('get')
            ->with('templating')
            ->willReturn($templating);
        $response = new Response('foo');
        $event = new GetResponseForControllerResultEvent($this->getKernelMock(), $this->getRequestWithTemplate(), HttpKernelInterface::MASTER_REQUEST, $response);
        $event->setControllerResult(null);
        $listener = new TemplateListener($container);
        $listener->onKernelView($event);
    }

    public function testOnKernelControllerWithClosureController()
    {
        $guesser = $this->getMockBuilder('\Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser')->disableOriginalConstructor()->getMock();
        $container = $this->getContainerMock();
        $container->expects($this->any())
                  ->method('get')
                  ->with('sensio_framework_extra.view.guesser')
                  ->willReturn($guesser);
        $listener = new TemplateListener($container);
        $controller = function () {};
        $request = $this->getRequestWithTemplate();
        $event = new FilterControllerEvent($this->getKernelMock(), $controller, $request, HttpKernelInterface::MASTER_REQUEST);
        $listener->onKernelController($event);
        $this->assertNull($request->attributes->get('_template')->getTemplate());
    }

    private function getContainerMock()
    {
        return $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')->getMock();
    }

    private function getKernelMock()
    {
        return $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernelInterface')->getMock();
    }

    private function getTemplatingMock()
    {
        return $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')->getMock();
    }

    private function getRequestWithTemplate()
    {
        $template = new Template(array());
        $request = new Request();
        $request->attributes->set('_template', $template);

        return $request;
    }
}

<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Handles the Template annotation for actions.
 *
 * Depends on pre-processing of the ControllerListener.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TemplateListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The service container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Guesses the template name to render and its variables and adds them to
     * the request object.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $template = $request->attributes->get('_template');

        // no @Template present
        if (null === $template) {
            return;
        }

        // we need the @Template annotation object or we cannot continue
        if (!$template instanceof Template) {
            return;
        }

        $template->setOwner($controller = $event->getController());

        // when no template has been given, try to resolve it based on the controller
        if (null === $template->getTemplate()) {
            $guesser = $this->container->get('sensio_framework_extra.view.guesser');
            $template->setTemplate($guesser->guessTemplateName($controller, $request, $template->getEngine()));
        }
    }

    /**
     * Renders the template and initializes a new response object with the
     * rendered template content.
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        /* @var Template $template */
        $request = $event->getRequest();
        $template = $request->attributes->get('_template');

        if (null === $template) {
            return;
        }

        $parameters = $event->getControllerResult();
        $owner = $template->getOwner();
        list($controller, $action) = $owner;

        // when the annotation declares no default vars and the action returns
        // null, all action method arguments are used as default vars
        if (null === $parameters) {
            $parameters = $this->resolveDefaultParameters($request, $template, $controller, $action);
        }

        // attempt to render the actual response
        $templating = $this->container->get('templating');

        if ($template->isStreamable()) {
            $callback = function () use ($templating, $template, $parameters) {
                return $templating->stream($template->getTemplate(), $parameters);
            };

            $event->setResponse(new StreamedResponse($callback));
        }

        // make sure the owner (controller+dependencies) is not cached or stored elsewhere
        $template->setOwner(array());

        $event->setResponse($templating->renderResponse($template->getTemplate(), $parameters));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -128),
            KernelEvents::VIEW => 'onKernelView',
        );
    }

    /**
     * @param Request  $request
     * @param Template $template
     * @param object   $controller
     * @param string   $action
     *
     * @return array
     */
    private function resolveDefaultParameters(Request $request, Template $template, $controller, $action)
    {
        $parameters = array();
        $arguments = $template->getVars();

        if (0 === count($arguments)) {
            $r = new \ReflectionObject($controller);

            $arguments = array();
            foreach ($r->getMethod($action)->getParameters() as $param) {
                $arguments[] = $param;
            }
        }

        // fetch the arguments of @Template.vars or everything if desired
        // and assign them to the designated template
        foreach ($arguments as $argument) {
            if ($argument instanceof \ReflectionParameter) {
                $parameters[$name = $argument->getName()] = !$request->attributes->has($name) && $argument->isDefaultValueAvailable() ? $argument->getDefaultValue() : $request->attributes->get($name);
            } else {
                $parameters[$argument] = $request->attributes->get($argument);
            }
        }

        return $parameters;
    }
}

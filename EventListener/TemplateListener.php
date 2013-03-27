<?php

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * The TemplateListener class handles the @Template annotation.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class TemplateListener implements EventSubscriberInterface
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
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
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_template')) {
            return;
        }

        if (!$configuration->getTemplate()) {
            $guesser = $this->container->get('sensio_framework_extra.view.guesser');
            $configuration->setTemplate($guesser->guessTemplateName($controller, $request, $configuration->getEngine()));
        }

        $request->attributes->set('_template', $configuration->getTemplate());
        $request->attributes->set('_template_vars', $configuration->getVars());
        $request->attributes->set('_template_streamable', $configuration->isStreamable());

        // all controller method arguments
        if (!$configuration->getVars()) {
            $r = new \ReflectionObject($controller[0]);

            $vars = array();
            foreach ($r->getMethod($controller[1])->getParameters() as $param) {
                $vars[] = $param->getName();
            }

            $request->attributes->set('_template_default_vars', $vars);
        }
    }

    /**
     * Renders the template and initializes a new response object with the
     * rendered template content.
     *
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $parameters = $event->getControllerResult();
        $templating = $this->container->get('templating');

        if (null === $parameters) {
            if (!$vars = $request->attributes->get('_template_vars')) {
                if (!$vars = $request->attributes->get('_template_default_vars')) {
                    return;
                }
            }

            $parameters = array();
            foreach ($vars as $var) {
                $parameters[$var] = $request->attributes->get($var);
            }
        }

        if (!is_array($parameters)) {
            return $parameters;
        }

        if (!$template = $request->attributes->get('_template')) {
            return $parameters;
        }

        if (!$request->attributes->get('_template_streamable')) {
            $event->setResponse($templating->renderResponse($template, $parameters));
        } else {
            $callback = function () use ($templating, $template, $parameters) {
                return $templating->stream($template, $parameters);
            };

            $event->setResponse(new StreamedResponse($callback));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -128),
            KernelEvents::VIEW => 'onKernelView',
        );
    }
}

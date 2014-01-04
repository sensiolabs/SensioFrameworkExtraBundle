<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Initializes the context from the request and sets request attributes based on a matching route.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RouterListener implements EventSubscriberInterface
{
    /**
     * Replace template to routeTemplate
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->has('_routeTemplate')) {
            $request->attributes->set('_template', $request->attributes->get('_routeTemplate'));
            $request->attributes->remove('_routeTemplate');
        }
    }

    /**
     * Mask _template from route to _routeTemplate
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $parameters = $request->attributes->get('_route_params');

        if (isset($parameters['_template'])) {
            $request->attributes->set('_routeTemplate', $parameters['_template']);
            $request->attributes->remove('_template');
            unset($parameters['_template']);
            $request->attributes->set('_route_params', $parameters);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -64),
            KernelEvents::REQUEST => array(array('onKernelRequest', 16)),
        );
    }
}

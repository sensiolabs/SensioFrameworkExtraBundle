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

use Sensio\Bundle\FrameworkExtraBundle\Controller\PreExecuteInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Calls PreExecuteInterface controllers
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 */
class PreExecuteListener implements EventSubscriberInterface
{
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

        $controllerObject = $controller[0];
        if (!$controllerObject instanceof PreExecuteInterface) {
            return;
        }

        $controllerObject->preExecute($event->getRequest(), $controller[1]);
    }

    public static function getSubscribedEvents()
      {
          return array(
              // slightly positive so that it runs before things like @Security
              KernelEvents::CONTROLLER => array('onKernelController', 10),
          );
      }
}

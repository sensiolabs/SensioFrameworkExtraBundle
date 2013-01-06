<?php
 
namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;
 
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * The AjaxListener class handles the "@Ajax" annotation.
 * 
 * @author      Víctor Marqués <victmarqm@gmail.com>
 */
class AjaxListener
{ 
    /**
     * Checks if the controller has the @Ajax annotation and throws an 
     * HttpException with status code 403 if the request is not an AJAX request.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (false === is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        if ($request->attributes->get('_ajax')) {
            if (false === $request->isXmlHttpRequest()) {
                throw new HttpException(403,'This action only responds to AJAX Requests.');
            }
        }    
    }
}
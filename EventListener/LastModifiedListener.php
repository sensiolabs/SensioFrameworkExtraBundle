<?php

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\LastModified;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The LastModifiedListener handles the @LastModified annotation.
 *
 * @author Alexandr Sidorov <asidorov01@gmail.com>
 * @package Sensio\Bundle\FrameworkExtraBundle\EventListener
 */
class LastModifiedListener implements EventSubscriberInterface
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var \DateTime
     */
    protected $lastModifiedDate;

    /**
     * Handle If-Modified-Since headers in request.
     * Prevents controller call if content is not modified.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->has('_last_modified')) {

            /* @var $paramBug LastModified */
            $configuration = $request->attributes->get('_last_modified');

            $this->lastModifiedDate = $request->attributes->get($configuration->getParam())->{$configuration->getMethod()}();

            $response = new Response();
            $response->setLastModified($this->lastModifiedDate);

            if ($response->isNotModified($event->getRequest())) {
                $this->response = $response;

                //Throw exception to avoid running controller
                throw new LastModifiedException(304);
            }
        }
    }

    /**
     * Modifies the response to add Last-Modified header.
     *
     * @param FilterResponseEvent $event The notified event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (isset($this->lastModifiedDate)) {
            $event->getResponse()->setLastModified($this->lastModifiedDate);
        }
    }

    /**
     * Catch up LastModifiedException and overwrite response with LastModified header.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof LastModifiedException) {
            $event->setResponse($this->response);
            $event->stopPropagation();
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse',
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }
}

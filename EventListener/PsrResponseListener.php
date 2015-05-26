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

use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Converts PSR-7 Response to HttpFoundation Response using the bridge.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class PsrResponseListener implements EventSubscriberInterface
{
    /**
     * @var HttpFoundationFactoryInterface
     */
    private $httpFoundationFactory;

    public function __construct(HttpFoundationFactoryInterface $httpFoundationFactory)
    {
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    /**
     * Do the conversion if applicable and update the response of the event.
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $controllerResult = $event->getControllerResult();

        if (!$controllerResult instanceof ResponseInterface) {
            return;
        }

        $event->setResponse($this->httpFoundationFactory->createResponse($controllerResult));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'onKernelView',
        );
    }
}

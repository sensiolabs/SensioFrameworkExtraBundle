<?php

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * The JsonListener class handles the @Json annotations.
 *
 * @author Grégoire Passault <g.passault@gmail.com>
 */
class JsonListener
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
     * Renders the JSON-encoded data returned by the controller.
     *
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();

        if ($configuration = $request->attributes->get('_json')) {
            $charset = $this->container->get('kernel')->getCharset();
            $response = new Response;
            $response->headers->set('Content-type', 'application/json; charset=' . $charset);
            $response->setContent(json_encode($data));
            $event->setResponse($response);
        }
    }
}

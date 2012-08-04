<?php

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
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
 * The CacheListener class has the responsibility to modify the
 * Response object when a controller uses the "@Cache" annotation.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class CacheListener
{
    /**
     * Modifies the response to apply HTTP expiration header fields.
     *
     * @param FilterResponseEvent $event The notified event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$configuration = $event->getRequest()->attributes->get('_cache')) {
            return;
        }

        $response = $event->getResponse();

        if (!$response->isSuccessful()) {
            return;
        }

        if (null !== $configuration->getSMaxAge()) {
            $response->setSharedMaxAge($configuration->getSMaxAge());
        }

        if (null !== $configuration->getMaxAge()) {
            $response->setMaxAge($configuration->getMaxAge());
        }

        if (null !== $configuration->getExpires()) {
            $date = \DateTime::createFromFormat('U', strtotime($configuration->getExpires()), new \DateTimeZone('UTC'));
            $response->setExpires($date);
        }

        if (null !== $configuration->getVary()) {
            $response->setVary($configuration->getVary());
        }

        if ($configuration->isPublic()) {
            $response->setPublic();
        }

        $event->setResponse($response);
    }
}

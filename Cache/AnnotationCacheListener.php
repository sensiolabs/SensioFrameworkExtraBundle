<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Cache;

use Symfony\Component\EventDispatcher\EventInterface;
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
 * .
 *
 * The filter method must be connected to the core.response event.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class AnnotationCacheListener
{
    /**
     * Modifies the response to apply HTTP expiration header fields.
     *
     * @param  Event    $event   An Event instance
     * @return Response $reponse The modified Response instance
     */
    public function filter(EventInterface $event, Response $response)
    {
        if (!$configuration = $event->get('request')->attributes->get('_cache')) {
            return $response;
        }

        if (!$response->isSuccessful()) {
            return $response;
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

        if ($configuration->isPublic()) {
            $response->setPublic();
        }

        return $response;
    }
}

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

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * HttpCacheListener handles HTTP cache headers.
 *
 * It can be configured via the Cache annotation.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HttpCacheListener implements EventSubscriberInterface
{
    private $lastModifiedDates;
    private $etags;
    private $expressionLanguage;

    public function __construct()
    {
        $this->lastModifiedDates = new \SplObjectStorage();
        $this->etags = new \SplObjectStorage();
    }

    /**
     * Handles HTTP validation headers.
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if (!$configuration = $request->attributes->get('_cache')) {
            return;
        }

        $response = new Response();

        $lastModifiedDate = '';
        if ($configuration->getLastModified()) {
            $lastModifiedDate = $this->getExpressionLanguage()->evaluate($configuration->getLastModified(), $request->attributes->all());
            $response->setLastModified($lastModifiedDate);
        }

        $etag = '';
        if ($configuration->getETag()) {
            $etag = hash('sha256', $this->getExpressionLanguage()->evaluate($configuration->getETag(), $request->attributes->all()));
            $response->setETag($etag);
        }

        if ($response->isNotModified($request)) {
            $event->setController(function () use ($response) {
                return $response;
            });
        } else {
            if ($etag) {
                $this->etags[$request] = $etag;
            }
            if ($lastModifiedDate) {
                $this->lastModifiedDates[$request] = $lastModifiedDate;
            }
        }
    }

    /**
     * Modifies the response to apply HTTP cache headers when needed.
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_cache')) {
            return;
        }

        $response = $event->getResponse();

        // http://tools.ietf.org/html/draft-ietf-httpbis-p4-conditional-12#section-3.1
        if (!in_array($response->getStatusCode(), array(200, 203, 300, 301, 302, 304, 404, 410))) {
            return;
        }

        if (null !== $age = $configuration->getSMaxAge()) {
            if (!is_numeric($age)) {
                $now = microtime(true);

                $age = ceil(strtotime($configuration->getSMaxAge(), $now) - $now);
            }

            $response->setSharedMaxAge($age);
        }

        if (null !== $age = $configuration->getMaxAge()) {
            if (!is_numeric($age)) {
                $now = microtime(true);

                $age = ceil(strtotime($configuration->getMaxAge(), $now) - $now);
            }

            $response->setMaxAge($age);
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

        if (isset($this->lastModifiedDates[$request])) {
            $response->setLastModified($this->lastModifiedDates[$request]);

            unset($this->lastModifiedDates[$request]);
        }

        if (isset($this->etags[$request])) {
            $response->setETag($this->etags[$request]);

            unset($this->etags[$request]);
        }

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }

    private function getExpressionLanguage()
    {
        if (null === $this->expressionLanguage) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                throw new \RuntimeException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
            }
            $this->expressionLanguage = new ExpressionLanguage();
        }

        return $this->expressionLanguage;
    }
}

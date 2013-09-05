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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\LastModified;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * The LastModifiedListener handles the @LastModified annotation.
 *
 * @author Alexandr Sidorov <asidorov01@gmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class LastModifiedListener implements EventSubscriberInterface
{
    private $lastModifiedDates;
    private $expressionLanguage;

    public function __construct()
    {
        $this->lastModifiedDates = new \SplObjectStorage();
    }

    /**
     * Handles If-Modified-Since headers in request.
     * Prevents controller call if content is not modified.
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if (!$configuration = $request->attributes->get('_last_modified')) {
            return;
        }

        $lastModifiedDate = $this->getExpressionLanguage()->evaluate($configuration->getExpression(), $request->attributes->all());

        $response = new Response();
        $response->setLastModified($lastModifiedDate);

        if ($response->isNotModified($request)) {
            $event->setController(function () use ($response) {
                return $response;
            });
        } else {
            $this->lastModifiedDates[$request] = $lastModifiedDate;
        }
    }

    /**
     * Modifies the response to add a Last-Modified header.
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        if (isset($this->lastModifiedDates[$request])) {
            $event->getResponse()->setLastModified($this->lastModifiedDates[$request]);

            unset($this->lastModifiedDates[$request]);
        }
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

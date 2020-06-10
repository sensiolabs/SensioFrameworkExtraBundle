<?php

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ContentSafeListener implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_content_safe')) {
            return;
        }

        if ($request->preferSafeContent()) {
            $event->getResponse()->setContentSafe();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}

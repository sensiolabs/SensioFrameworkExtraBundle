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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Csrf;
use Sensio\Bundle\FrameworkExtraBundle\Exception\InvalidCsrfTokenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * CsrfListener handles csrf restrictions on actions.
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class CsrfListener implements EventSubscriberInterface
{
    private $csrfTokenManager;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager = null)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if (!$configuration = $request->attributes->get('_csrf')) {
            return;
        }
        /* @var $configuration Csrf */

        if (null === $this->csrfTokenManager) {
            throw new \LogicException('To use the @Csrf tag, you need to install the "symfony/security-csrf".');
        }

        $tokenValue = $request->get($configuration->getParam());

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($configuration->getIntention(), $tokenValue))) {
            $e = new InvalidCsrfTokenException();
            $e->setValidToken($this->csrfTokenManager->getToken($configuration->getIntention())->getValue());

            throw $e;
        }
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => 'onKernelController');
    }
}

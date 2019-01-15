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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Exception\RuntimeException as DependencyInjectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Handles the IsGranted annotation on controllers.
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 */
class IsGrantedListener implements EventSubscriberInterface
{
    private $argumentNameConverter;
    private $authChecker;

    public function __construct(ArgumentNameConverter $argumentNameConverter, AuthorizationCheckerInterface $authChecker = null)
    {
        $this->argumentNameConverter = $argumentNameConverter;
        $this->authChecker = $authChecker;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        /** @var $configurations IsGranted[] */
        if (!$configurations = $request->attributes->get('_is_granted')) {
            return;
        }

        $this->warnAboutAuthChecker();

        foreach ($configurations as $configuration) {
            // Only configurations WITHOUT subject.
            if (null === $configuration->getSubject()) {
                $this->checkIsGranted($configuration, null);
            }
        }
    }

    public function onKernelControllerArguments(FilterControllerArgumentsEvent $event)
    {
        $request = $event->getRequest();
        /** @var $configurations IsGranted[] */
        if (!$configurations = $request->attributes->get('_is_granted')) {
            return;
        }

        $this->warnAboutAuthChecker();

        $arguments = $this->argumentNameConverter->getControllerArguments($event);

        foreach ($configurations as $configuration) {
            // Only configurations WITH subject.
            if (null === $configuration->getSubject()) {
                continue;
            }

            if (!isset($arguments[$configuration->getSubject()])) {
                throw new \RuntimeException(sprintf('Could not find the subject "%s" for the @IsGranted annotation. Try adding a "$%s" argument to your controller method.', $configuration->getSubject(), $configuration->getSubject()));
            }

            $subject = $arguments[$configuration->getSubject()];

            $this->checkIsGranted($configuration, $subject);
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        /** @var $configurations IsGranted[] */
        if (!$configurations = $request->attributes->get('_is_granted')) {
            return;
        }

        $exception = $event->getException();

        if ($exception instanceof DependencyInjectionException && false !== strpos($exception->getMessage(), UserInterface::class)) {
            foreach ($configurations as $configuration) {
                if (null !== $configuration->getSubject()) {
                    $exception = new \RuntimeException(sprintf('Usage of both @IsGranted annotation with subject and %s type hint in controller argument is not supported. Use getUser() or isGranted() methods of %s instead.', UserInterface::class, AbstractController::class));

                    $event->setException($exception);
                }
            }
        }
    }

    private function warnAboutAuthChecker()
    {
        if (null === $this->authChecker) {
            throw new \LogicException('To use the @IsGranted tag, you need to install symfony/security-bundle and configure your security system.');
        }
    }

    private function checkIsGranted(IsGranted $isGranted, $subject)
    {
        if (!$this->authChecker->isGranted($isGranted->getAttributes(), $subject)) {
            $argsString = $this->getIsGrantedString($isGranted);

            $message = $isGranted->getMessage() ?: sprintf('Access Denied by controller annotation @IsGranted(%s)', $argsString);

            if ($statusCode = $isGranted->getStatusCode()) {
                throw new HttpException($statusCode, $message);
            }

            throw new AccessDeniedException($message);
        }
    }

    private function createMissingSubjectException(string $subject)
    {
        return new \RuntimeException(sprintf('Could not find the subject "%s" for the @IsGranted annotation. Try adding a "$%s" argument to your controller method.', $subject, $subject));
    }

    private function getIsGrantedString(IsGranted $isGranted)
    {
        $attributes = array_map(function ($attribute) {
            return sprintf('"%s"', $attribute);
        }, (array) $isGranted->getAttributes());
        if (1 === \count($attributes)) {
            $argsString = reset($attributes);
        } else {
            $argsString = sprintf('[%s]', implode(', ', $attributes));
        }

        if (null !== $isGranted->getSubject()) {
            $argsString = sprintf('%s, %s', $argsString, $isGranted->getSubject());
        }

        return $argsString;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments',
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}

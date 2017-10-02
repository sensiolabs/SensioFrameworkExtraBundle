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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Handles the IsGranted annotation on controllers.
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 */
class IsGrantedListener implements EventSubscriberInterface
{
    private $argumentMetadataFactory;
    private $authChecker;

    public function __construct(ArgumentMetadataFactoryInterface $argumentMetadataFactory, AuthorizationCheckerInterface $authChecker = null)
    {
        $this->argumentMetadataFactory = $argumentMetadataFactory;
        $this->authChecker = $authChecker;
    }

    public function onKernelControllerArguments(FilterControllerArgumentsEvent $event)
    {
        $request = $event->getRequest();
        /** @var $configurations IsGranted[] */
        if (!$configurations = $request->attributes->get('_is_granted')) {
            return;
        }

        if (null === $this->authChecker) {
            throw new \LogicException('To use the @IsGranted tag, you need to install symfony/security-bundle and configure your security system.');
        }

        $arguments = $this->getArguments($event);

        foreach ($configurations as $configuration) {
            $subject = null;
            if ($configuration->getSubject()) {
                if (!isset($arguments[$configuration->getSubject()])) {
                    throw new \RuntimeException(sprintf('Could not find the subject "%s" for the @IsGranted annotation. Try adding a "$%s" argument to your controller method.', $configuration->getSubject(), $configuration->getSubject()));
                }

                $subject = $arguments[$configuration->getSubject()];
            }

            if (!$this->authChecker->isGranted($configuration->getAttributes(), $subject)) {
                $argsString = $this->getIsGrantedString($configuration);

                $message = $configuration->getMessage() ?: sprintf('Access Denied by controller annotation @IsGranted(%s)', $argsString);

                if ($statusCode = $configuration->getStatusCode()) {
                    throw new HttpException($statusCode, $message);
                }

                throw new AccessDeniedException($message);
            }
        }
    }

    private function getArguments(FilterControllerArgumentsEvent $event)
    {
        $namedArguments = $event->getRequest()->attributes->all();
        $argumentMetadatas = $this->argumentMetadataFactory->createArgumentMetadata($event->getController());

        // loop over each argument value and its name from the metadata
        foreach ($event->getArguments() as $index => $argument) {
            if (!isset($argumentMetadatas[$index])) {
                throw new \LogicException(sprintf('Could not find any argument metadata for argument %d of the controller.', $index));
            }

            $namedArguments[$argumentMetadatas[$index]->getName()] = $argument;
        }

        return $namedArguments;
    }

    private function getIsGrantedString(IsGranted $isGranted)
    {
        $attributes = array_map(function ($attribute) {
            return sprintf('"%s"', $attribute);
        }, (array) $isGranted->getAttributes());
        if (1 === count($attributes)) {
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
        return array(KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments');
    }
}

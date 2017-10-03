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

use Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter;
use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * SecurityListener handles security restrictions on controllers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecurityListener implements EventSubscriberInterface
{
    private $argumentNameConverter;
    private $tokenStorage;
    private $authChecker;
    private $language;
    private $trustResolver;
    private $roleHierarchy;

    public function __construct(ArgumentNameConverter $argumentNameConverter, ExpressionLanguage $language = null, AuthenticationTrustResolverInterface $trustResolver = null, RoleHierarchyInterface $roleHierarchy = null, TokenStorageInterface $tokenStorage = null, AuthorizationCheckerInterface $authChecker = null)
    {
        $this->argumentNameConverter = $argumentNameConverter;
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->language = $language;
        $this->trustResolver = $trustResolver;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function onKernelControllerArguments(FilterControllerArgumentsEvent $event)
    {
        $request = $event->getRequest();
        if (!$configurations = $request->attributes->get('_security')) {
            return;
        }

        if (null === $this->tokenStorage || null === $this->trustResolver) {
            throw new \LogicException('To use the @Security tag, you need to install the Symfony Security bundle.');
        }

        if (null === $this->tokenStorage->getToken()) {
            throw new \LogicException('To use the @Security tag, your controller needs to be behind a firewall.');
        }

        if (null === $this->language) {
            throw new \LogicException('To use the @Security tag, you need to use the Security component 2.4 or newer and install the ExpressionLanguage component.');
        }

        foreach ($configurations as $configuration) {
            if (!$this->language->evaluate($configuration->getExpression(), $this->getVariables($event))) {
                if ($statusCode = $configuration->getStatusCode()) {
                    throw new HttpException($statusCode, $configuration->getMessage());
                }

                throw new AccessDeniedException($configuration->getMessage() ?: sprintf('Expression "%s" denied access.', $configuration->getExpression()));
            }
        }
    }

    // code should be sync with Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter
    private function getVariables(FilterControllerArgumentsEvent $event)
    {
        $request = $event->getArguments();
        $token = $this->tokenStorage->getToken();

        if (null !== $this->roleHierarchy) {
            $roles = $this->roleHierarchy->getReachableRoles($token->getRoles());
        } else {
            $roles = $token->getRoles();
        }

        $variables = array(
            'token' => $token,
            'user' => $token->getUser(),
            'object' => $request,
            'subject' => $request,
            'request' => $request,
            'roles' => array_map(function ($role) { return $role->getRole(); }, $roles),
            'trust_resolver' => $this->trustResolver,
            // needed for the is_granted expression function
            'auth_checker' => $this->authChecker,
        );

        $controllerArguments = $this->argumentNameConverter->getControllerArguments($event);

        if ($diff = array_intersect(array_keys($variables), array_keys($controllerArguments))) {
            $singular = 1 === count($diff);
            throw new \RuntimeException(sprintf('Request attribute%s "%s" cannot be defined as %s collide%s with built-in security expression variables.', $singular ? '' : 's', implode('", "', $diff), $singular ? 'it' : 'they', $singular ? 's' : ''));
        }

        // controller variables should also be accessible
        return array_merge($controllerArguments, $variables);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments');
    }
}

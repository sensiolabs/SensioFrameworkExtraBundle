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

use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * SecurityListener handles security restrictions on controllers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecurityListener implements EventSubscriberInterface
{
    private $tokenStorage;
    private $authChecker;
    private $language;
    private $trustResolver;
    private $roleHierarchy;

    public function __construct(SecurityContextInterface $securityContext = null, ExpressionLanguage $language = null, AuthenticationTrustResolverInterface $trustResolver = null, RoleHierarchyInterface $roleHierarchy = null, TokenStorageInterface $tokenStorage = null, AuthorizationCheckerInterface $authChecker = null)
    {
        $this->tokenStorage = $tokenStorage ?: $securityContext;
        $this->authChecker = $authChecker ?: $securityContext;
        $this->language = $language;
        $this->trustResolver = $trustResolver;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if (!$configuration = $request->attributes->get('_security')) {
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

        if (!$this->language->evaluate($configuration->getExpression(), $this->getVariables($request))) {
            throw new AccessDeniedException(sprintf('Expression "%s" denied access.', $configuration->getExpression()));
        }
    }

    // code should be sync with Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter
    private function getVariables(Request $request)
    {
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

        // controller variables should also be accessible
        return array_merge($request->attributes->all(), $variables);
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => 'onKernelController');
    }
}

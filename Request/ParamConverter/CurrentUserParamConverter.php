<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DateTime;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Inject the current user into controller methods.
 *
 * Alternative to getUser in base controller class, which relies on the
 * whole service container.
 *
 * Does not require controllers to depend upon the entire
 * SecurityContext object.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class CurrentUserParamConverter implements ParamConverterInterface
{

    /**
     * @var SecurityContextInterface
     */
    private $security;

    public function __construct(SecurityContextInterface $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotFoundHttpException When invalid date given
     */
    public function apply(Request $request, ParamConverter $configuration)
    {

        $param = $configuration->getName();

        $currentUser = $this->getUser();

        if (!$currentUser && !$configuration->isOptional()) {
            throw new AccessDeniedException('A user was expected in the SecurityContext. Consider configuring a firewall in security.yml or making this argument optional');
        }

        $request->attributes->set($param, $currentUser);

        return true;
    }

    /**
     * Get a user from the Security Context
     *
     * @return mixed
     */
    private function getUser()
    {

        if (null === $token = $this->security->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        return $user;
    }



    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        $userInterfaceClass = 'Symfony\Component\Security\Core\User\UserInterface';
        $configuredClass = $configuration->getClass();
        $interfaces = class_implements($configuredClass);

        return in_array($userInterfaceClass, $interfaces) || ($configuredClass == $userInterfaceClass);

    }
}

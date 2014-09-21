<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter\CurrentUser\Doubles;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class ConcreteDomainUser implements DomainUserInterface
{
    public function getRoles() {}
    public function getPassword() {}
    public function getSalt() {}
    public function getUsername() {}
    public function eraseCredentials() {}
}
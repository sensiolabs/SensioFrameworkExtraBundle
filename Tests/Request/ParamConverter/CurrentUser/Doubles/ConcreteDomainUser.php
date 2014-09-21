<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter\CurrentUser\Doubles;

class ConcreteDomainUser implements DomainUserInterface
{
    public function getRoles() {}
    public function getPassword() {}
    public function getSalt() {}
    public function getUsername() {}
    public function eraseCredentials() {}
}

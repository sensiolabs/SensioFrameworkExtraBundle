<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

#[Security("is_granted('ROLE_USER')")]
class FooControllerSecurityAttributeAtClass
{
    public function barAction($foo)
    {
    }
}

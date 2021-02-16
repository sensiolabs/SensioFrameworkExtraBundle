<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class FooControllerSecurityAtMethod
{
    /**
     * @Security("is_granted('ROLE_USER') and is_granted('FOO_SHOW', foo)")
     */
    public function barAction($foo)
    {
    }
}

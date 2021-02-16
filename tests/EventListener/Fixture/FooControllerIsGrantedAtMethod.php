<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class FooControllerIsGrantedAtMethod
{
    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("FOO_SHOW", subject="foo")
     */
    public function barAction($foo)
    {
    }
}

<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class FooControllerCacheOnInvokableController
{
    const INVOKE_SMAXAGE = 15;

    /**
     * @Cache(smaxage="15")
     */
    public function __invoke()
    {
    }
}

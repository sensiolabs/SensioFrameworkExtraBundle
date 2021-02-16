<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class FooControllerMultipleCacheAttributeAtMethod
{
    #[Cache()]
    #[Cache()]
    public function barAction()
    {
    }
}

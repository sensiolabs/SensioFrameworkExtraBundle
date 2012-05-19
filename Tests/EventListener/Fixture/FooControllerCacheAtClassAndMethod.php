<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * @Cache(smaxage="20")
 */
class FooControllerCacheAtClassAndMethod
{
    const CLASS_SMAXAGE = 20;
    const METHOD_SMAXAGE = 15;

    /**
     * @Cache(smaxage="15")
     */
    public function barAction()
    {
    }

    public function bar2Action()
    {
    }
}

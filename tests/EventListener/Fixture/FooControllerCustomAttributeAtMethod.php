<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

class FooControllerCustomAttributeAtMethod
{
    const METHOD_CUSTOM = 'func';

    #[CustomAttribute(custom: 'func')]
    public function barAction()
    {
    }
}

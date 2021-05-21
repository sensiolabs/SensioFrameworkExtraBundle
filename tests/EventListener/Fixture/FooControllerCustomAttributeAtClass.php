<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

#[CustomAttribute(custom: 'class')]
class FooControllerCustomAttributeAtClass
{
    const CLASS_CUSTOM = 'class';

    public function barAction()
    {
    }
}

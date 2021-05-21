<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

#[CustomAttribute(custom: 'class')]
class FooControllerCustomAttributeAtClassAndMethod
{
    const CLASS_CUSTOM = 'class';
    const METHOD_CUSTOM = 'func';

    #[CustomAttribute(custom: 'func')]
    public function barAction()
    {
    }

    public function bar2Action()
    {
    }
}

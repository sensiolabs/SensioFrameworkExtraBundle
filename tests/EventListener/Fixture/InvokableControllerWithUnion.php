<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

final class InvokableControllerWithUnion
{
    public function __invoke(int | \DateTime | string $date)
    {
    }
}

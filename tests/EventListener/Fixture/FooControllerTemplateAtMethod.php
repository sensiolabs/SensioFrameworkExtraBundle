<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FooControllerTemplateAtMethod
{
    /**
     * @Template("templates/bar.html.twig", vars={"foo"})
     */
    public function barAction($foo)
    {
    }
}

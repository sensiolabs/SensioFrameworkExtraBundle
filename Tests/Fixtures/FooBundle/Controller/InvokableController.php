<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tests\Fixtures\FooBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route(service="test.invokable.predefined")
 */
class InvokableController
{
    /**
     * @Route("/invokable/predefined/service/")
     * @Template("FooBundle:Invokable:predefined.html.twig")
     */
    public function __invoke()
    {
        return array(
            'foo' => 'bar',
        );
    }
}

<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Routing\Fixtures;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(name="routenameprefix")
 */
class PrefixNamedController
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
    }

    /**
     * @Route("/new", name="new")
     */
    public function newAction()
    {
    }
}
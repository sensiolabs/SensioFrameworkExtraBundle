<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Routing\Fixtures;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/base")
 */
class BuzzController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
    }

    /**
     * @Route("/new", name="new")
     * @Method({"GET", "POST"})
     */
    public function newAction()
    {
    }

    /**
     * @Route("/foo", name="foo")
     * @Method("PUT|POST")
     */
    public function fooAction()
    {
    }

}

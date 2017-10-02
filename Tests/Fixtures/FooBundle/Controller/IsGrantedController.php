<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\FooBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IsGrantedController
{
    /**
     * @Route("/is_granted/anonymous")
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     */
    public function someAction()
    {
        return new Response('yay1');
    }

    /**
     * @Route("/is_granted/request/attribute/args/{a}")
     * @IsGranted("ISGRANTED_VOTER", subject="a")
     */
    public function some2Action($a)
    {
        return new Response('yay2');
    }

    /**
     * @Route("/is_granted/resolved/args")
     * @IsGranted("ISGRANTED_VOTER", subject="foo")
     */
    public function some3Action(Request $foo)
    {
        return new Response('yay3');
    }
}

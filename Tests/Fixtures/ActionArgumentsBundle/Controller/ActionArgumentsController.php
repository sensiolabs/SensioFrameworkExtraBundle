<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tests\Fixtures\ActionArgumentsBundle\Controller;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/action-arguments", service="test.action_arguments")
 */
class ActionArgumentsController
{
    /**
     * @Route("/invoke/")
     */
    public function __invoke(RequestInterface $request, MessageInterface $message, ServerRequestInterface $serverRequest)
    {
        return new Response('<html><body>ok</body></html>');
    }

    /**
     * @Route("/normal/")
     */
    public function normalAction(RequestInterface $request, MessageInterface $message, ServerRequestInterface $serverRequest)
    {
        return new Response('<html><body>ok</body></html>');
    }
}

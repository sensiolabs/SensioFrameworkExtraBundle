<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

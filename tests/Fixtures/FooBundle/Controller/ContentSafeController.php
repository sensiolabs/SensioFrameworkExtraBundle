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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ContentSafe;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/content-safe")
 */
class ContentSafeController
{
    /**
     * @Route("")
     * @ContentSafe()
     */
    public function safeAction()
    {
        return new Response('<html><body>ok</body></html>');
    }

    /**
     * @Route("/non-safe")
     */
    public function nonSafeAction()
    {
        return new Response('<html><body>ok</body></html>');
    }
}

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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="test.templateandcache")
 */
class TemplateAndCacheController
{

    /**
    * @Route("/templateandcache")
    * @Template()
    * @Cache(etag="'ThisIsMyETAG'")
    */
    public function templateAndCacheAction()
    {

    }

    /**
    * @Route("/cacheonly")
    * @Cache(etag="'ThisIsMyETAG'")
    */
    public function cacheOnlyAction()
    {
      return new Response('<html><body>This is to be cached</body></html>');
    }

}

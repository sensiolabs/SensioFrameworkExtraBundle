<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @author Kris Wallsmith <kris.wallsmith@symfony.com>
 */
class Route extends BaseRoute
{
    protected $service;

    public function setService($service)
    {
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }
}

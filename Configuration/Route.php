<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;

/**
 * @author Kris Wallsmith <kris@symfony.com>
 * @Annotation
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

    /**
     * Multiple route annotations are allowed
     *
     * @return bool
     * @see ConfigurationInterface
     */
    public function allowArray()
    {
        return true;
    }
}

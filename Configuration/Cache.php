<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * 
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class Cache implements ConfigurationInterface
{
    protected $expires;
    protected $maxage;
    protected $smaxage;

    public function getExpires()
    {
        return $this->expires;
    }

    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    public function setMaxAge($maxage)
    {
        $this->maxage = $maxage;
    }

    public function getMaxAge()
    {
        return $this->maxage;
    }

    public function setSMaxAge($smaxage)
    {
        $this->smaxage = $smaxage;
    }

    public function getSMaxAge()
    {
        return $this->smaxage;
    }

    public function getAliasName()
    {
        return 'cache';
    }
}

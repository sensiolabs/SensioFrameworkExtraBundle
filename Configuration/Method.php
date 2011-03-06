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
class Method implements ConfigurationInterface
{
    protected $methods = array();

    public function getMethods()
    {
        return $this->methods;
    }

    public function setMethods($methods)
    {
        $this->methods = is_array($methods) ? $methods : array($methods);
    }

    public function setValue($methods)
    {
        $this->setMethods($methods);
    }

    public function getAliasName()
    {
        return 'method';
    }
}

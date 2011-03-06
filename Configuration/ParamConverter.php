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
 * @extra:ParamConverter("post", class="BlogBundle:Post")
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class ParamConverter implements ConfigurationInterface
{
    protected $name;
    protected $class;
    protected $options = array();

    public function getName()
    {
        return $this->name;
    }

    public function setValue($name)
    {
        $this->setName($name);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getAliasName()
    {
        return 'converters';
    }
}

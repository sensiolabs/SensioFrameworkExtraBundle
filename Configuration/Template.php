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
class Template implements ConfigurationInterface
{
    protected $template;
    protected $vars = array();

    public function getVars()
    {
        return $this->vars;
    }

    public function setVars($vars)
    {
        $this->vars = $vars;
    }

    public function setValue($template)
    {
        $this->setTemplate($template);
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getAliasName()
    {
        return 'template';
    }
}

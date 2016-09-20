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

/**
 * The Csrf class handles the Csrf annotation.
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 *
 * @Annotation
 * @Target("METHOD")
 */
class Csrf extends ConfigurationAnnotation
{
    private $intention = 'default';

    private $param = '_token';

    public function getIntention()
    {
        return $this->intention;
    }

    public function setIntention($intention)
    {
        $this->intention = $intention;
    }

    public function getParam()
    {
        return $this->param;
    }

    public function setParam($param)
    {
        $this->param = $param;
    }

    public function setValue($intention)
    {
        $this->setIntention($intention);
    }

    public function getAliasName()
    {
        return 'csrf';
    }

    public function allowArray()
    {
        return false;
    }
}

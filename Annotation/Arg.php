<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *     @Attribute("value", type="string", required=true),
 * })
 */
class Arg
{
    private $arg;
    private $options = array();

    public function __construct(array $values)
    {
        $this->arg = $values['value'];
        unset($values['value']);
        $this->options = $values;
    }

    public function getArg()
    {
        return $this->arg;
    }

    public function getOptions()
    {
        return $this->options;
    }
}

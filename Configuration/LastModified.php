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
 * The LastModified class handles the @LastModified annotation parts.
 *
 * @LastModified("post.getUpdatedAt()")
 *
 * @author Alexandr Sidorov <asidorov01@gmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 * @Annotation
 */
class LastModified extends ConfigurationAnnotation
{
    protected $expression;

    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function setValue($expression)
    {
        $this->setExpression($expression);
    }

    /**
     * Returns the alias name for an annotated configuration.
     *
     * @return string
     */
    function getAliasName()
    {
        return 'last_modified';
    }

    /**
     * Returns whether multiple annotations of this type are allowed
     *
     * @return Boolean
     */
    function allowArray()
    {
        return false;
    }
}

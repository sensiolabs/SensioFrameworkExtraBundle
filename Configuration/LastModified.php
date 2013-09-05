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
 * @LastModified("post", method="getUpdatedAt")
 *
 * @author Alexandr Sidorov <asidorov01@gmail.com>
 * @Annotation
 */
class LastModified extends ConfigurationAnnotation
{
    /**
     * Method in entity, must return \DateTime.
     *
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $param;

    /**
     * @param string $param
     */
    public function setParam($param)
    {
        $this->param = $param;
    }

    /**
     * @return string
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
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

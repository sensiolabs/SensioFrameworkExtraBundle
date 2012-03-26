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
 * The Cache class handles the @Cache annotation parts.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @Annotation
 */
class Cache extends ConfigurationAnnotation
{
    /**
     * The expiration date as a valid date for the strtotime() function.
     *
     * @var string
     */
    protected $expires;

    /**
     * The number of seconds that the response is considered fresh by a private
     * cache like a web browser.
     *
     * @var integer
     */
    protected $maxage;

    /**
     * The number of seconds that the response is considered fresh by a public
     * cache like a reverse proxy cache.
     *
     * @var integer
     */
    protected $smaxage;

    /**
     * Whether or not the response is public or not.
     *
     * @var integer
     */
    protected $public;

    /**
     * Additional "Vary:"-headers
     *
     * @var array
     */
    protected $vary = array();

    /**
     * Returns the expiration date for the Expires header field.
     *
     * @return string
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Sets the expiration date for the Expires header field.
     *
     * @param string $expires A valid php date
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * Sets the number of seconds for the max-age cache-control header field.
     *
     * @param integer $maxage A number of seconds
     */
    public function setMaxAge($maxage)
    {
        $this->maxage = $maxage;
    }

    /**
     * Returns the number of seconds the response is considered fresh by a
     * private cache.
     *
     * @return integer
     */
    public function getMaxAge()
    {
        return $this->maxage;
    }

    /**
     * Sets the number of seconds for the s-maxage cache-control header field.
     *
     * @param integer $smaxage A number of seconds
     */
    public function setSMaxAge($smaxage)
    {
        $this->smaxage = $smaxage;
    }

    /**
     * Returns the number of seconds the response is considered fresh by a
     * public cache.
     *
     * @return integer
     */
    public function getSMaxAge()
    {
        return $this->smaxage;
    }

    /**
     * Returns whether or not a response is public.
     *
     * @return Boolean
     */
    public function isPublic()
    {
        return (Boolean) $this->public;
    }

    /**
     * Sets a response public.
     *
     * @param Boolean $public A boolean value
     */
    public function setPublic($public)
    {
        $this->public = (Boolean) $public;
    }

    /**
     * Returns the custom "Vary"-headers
     *
     * @return array
     */
    public function getVary()
    {
        return $this->vary;
    }

    /**
     * Add additional "Vary:"-headers
     *
     * @param array $vary
     */
    public function setVary($vary)
    {
        $this->vary = $vary;
    }

    /**
     * Returns the annotation alias name.
     *
     * @return string
     * @see ConfigurationInterface
     */
    public function getAliasName()
    {
        return 'cache';
    }
}

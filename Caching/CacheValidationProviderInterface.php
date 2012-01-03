<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Caching;

/**
 * The CacheValidationProviderInterface interface.
 * 
 * It must be implemented for each class used in "validation" attribute
 * of a Cache annotation.
 * 
 */
interface CacheValidationProviderInterface
{
    /**
     * Returns resource's eTag.
     *
     * @return string
     */
    function getETag();

    /**
     * Returns resource's last modification date.
     *
     * @return DateTime
     */
    function getLastModified();

    /**
     * The process method is used to generate the Etag and/or 
     * the last modification date.
     *
     * @return array
     */
    function process();
}

<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

/**
 * Marks a response with both the Preference-Applied and Vary response headers.
 *
 * The request needs to get served over https for the headers to get added.
 *
 * @Annotation
 */
class ContentSafe extends ConfigurationAnnotation
{
    public function getAliasName()
    {
        return 'content_safe';
    }

    public function allowArray()
    {
        return false;
    }
}

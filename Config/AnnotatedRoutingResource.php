<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Config;

class AnnotatedRoutingResource implements \Serializable
{
    private $class;
    private $filePath;
    private $annotationMetadata = array();

    public function __construct($class, $path, array $annotationMetadata)
    {
        $this->class = $class;
        $this->filePath = $path;
        $this->annotationMetadata = $annotationMetadata;
    }

    public function __toString()
    {
        return 'routing.annotations.'.$this->class;
    }

    public function serialize()
    {
        return serialize(array($this->class, $this->filePath, $this->annotationMetadata));
    }

    public function unserialize($serialized)
    {
        list($this->class, $this->filePath, $this->annotationMetadata) = unserialize($serialized);
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }
}
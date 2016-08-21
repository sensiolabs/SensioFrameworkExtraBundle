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

use Symfony\Component\Config\Resource\ResourceInterface;
use Doctrine\Common\Annotations\Reader;

class AnnotatedRoutingResource implements ResourceInterface, \Serializable
{
    private $class;
    private $filePath;
    private $annotationMetadata = array();

    public function __construct(\ReflectionClass $reflectionClass, Reader $reader)
    {
        $this->class = $reflectionClass->name;
        $this->filePath = $reflectionClass->getFileName();
        $this->annotationMetadata = $this->calculateAnnotationsFingerprint($reflectionClass, $reader);
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

    private function calculateAnnotationsFingerprint(\ReflectionClass $class, Reader $reader)
    {
        $metadata = array(
            'class' => array(),
            'methods' => array()
        );

        $classAnnotations = $reader->getClassAnnotations($class);
        foreach ($classAnnotations as $classAnnotation) {
            // cast to an array, as a convenient way to get a "fingerprint"
            // of all of the properties on the annotations objects
            $metadata['class'][] = (array) $classAnnotation;
        }

        foreach ($class->getMethods() as $method) {
            $metadata['methods'][$method->name] = [];
            foreach ($reader->getMethodAnnotations($method) as $annot) {
                $metadata['methods'][$method->name][] = (array) $annot;
            }
        }

        return $metadata;
    }
}
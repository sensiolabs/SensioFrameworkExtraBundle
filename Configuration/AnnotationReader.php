<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

use Doctrine\Common\Annotations\AnnotationReader as BaseAnnotationReader;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * .
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AnnotationReader extends BaseAnnotationReader
{
    public function getMethodAnnotations(\ReflectionMethod $method)
    {
        $this->setAutoloadAnnotations(true);
        $this->setAnnotationCreationFunction(function ($name, $values)
        {
            $r = new \ReflectionClass($name);
            if (!$r->implementsInterface('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ConfigurationInterface')) {
                return null;
            }

            $configuration = new $name();
            foreach ($values as $key => $value) {
                if (!method_exists($configuration, $method = 'set'.$key)) {
                    throw new \BadMethodCallException(sprintf("Unknown annotation attribute '%s' for '%s'.", ucfirst($key), get_class($this)));
                }
                $configuration->$method($value);
            }

            return $configuration;
        });

        $this->setDefaultAnnotationNamespace('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\');
        $configurations = parent::getMethodAnnotations($method);
        $this->setAnnotationCreationFunction(function ($name, $values) { return null; });

        return $configurations;
    }
}

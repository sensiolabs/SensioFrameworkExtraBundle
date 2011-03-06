<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\AnnotationReader as ConfigurationAnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * AnnotatedRouteControllerLoader is an implementation of AnnotationClassLoader
 * that sets the '_controller' default based on the class and method names.
 *
 * It also parse the @extra:Method annotation.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AnnotatedRouteControllerLoader extends AnnotationClassLoader
{
    protected $configReader;

    public function __construct(AnnotationReader $reader, ConfigurationAnnotationReader $configReader)
    {
        $this->configReader = $configReader;

        parent::__construct($reader);
    }

    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method)
    {
        // controller
        $classAnnot = $this->reader->getClassAnnotation($class, $this->annotationClass);
        if ($classAnnot && $service = $classAnnot->getService()) {
            $route->setDefault('_controller', $service.':'.$method->getName());
        } else {
            $route->setDefault('_controller', $class->getName().'::'.$method->getName());
        }

        // requirements (@extra:Method)
        foreach ($this->configReader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof Method) {
                $route->setRequirement('_method', implode('|', $configuration->getMethods()));
            }
        }
    }
}

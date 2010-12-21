<?php

namespace Bundle\Sensio\FrameworkExtraBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameConverter;
use Doctrine\Common\Annotations\AnnotationReader;
use Bundle\Sensio\FrameworkExtraBundle\Configuration\AnnotationReader as ConfigurationAnnotationReader;
use Bundle\Sensio\FrameworkExtraBundle\Configuration\Method;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * AnnotatedRouteControllerLoader is an implementation of AnnotationClassLoader
 * that sets the '_controller' default based on the class and method names.
 *
 * It also parse the @Method annotation.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class AnnotatedRouteControllerLoader extends AnnotationClassLoader
{
    protected $converter;

    public function __construct(ControllerNameConverter $converter, AnnotationReader $reader)
    {
        $this->converter = $converter;

        parent::__construct($reader);
    }

    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method)
    {
        // controller
        $classAnnot = $this->reader->getClassAnnotation($class, $this->annotationClass);
        if ($classAnnot && $service = $classAnnot->getService()) {
            $route->setDefault('_controller', $service.':'.$method->getName());
        } else {
            $route->setDefault('_controller', $this->converter->toShortNotation($class->getName().'::'.$method->getName()));
        }

        // requirements (@Method)
        $reader = new ConfigurationAnnotationReader();
        foreach ($reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof Method) {
                $route->setRequirement('_method', implode('|', $configuration->getMethods()));
            }
        }
    }
}

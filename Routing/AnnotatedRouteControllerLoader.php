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
    /**
     * @var Sensio\Bundle\FrameworkExtraBundle\Configuration\AnnotationReader
     */
    protected $configReader;

    /**
     * Constructor.
     *
     * @param AnnotationReader $reader An AnnotationReader instance
     * @param ConfigurationAnnotationReader $configReader A ConfigurationAnnotationReader instance
     */
    public function __construct(AnnotationReader $reader, ConfigurationAnnotationReader $configReader)
    {
        $this->configReader = $configReader;

        parent::__construct($reader);
    }

    /**
     * Configures the _controller default parameter and eventually the _method 
     * requirement of a given Route instance.
     *
     * @param Route $route A Route instance
     * @param ReflectionClass $class A ReflectionClass instance
     * @param ReflectionMethod $method A ReflectionClass method
     */
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        // controller
        $classAnnot = $this->reader->getClassAnnotation($class, $this->routeAnnotationClass);
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

    /**
     * Makes the default route name more sane by removing common keywords.
     *
     * @param  ReflectionClass $class A ReflectionClass instance
     * @param  ReflectionMethod $method A ReflectionMethod instance
     * @return string
     */
    public function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        $routeName = parent::getDefaultRouteName($class, $method);

        return str_replace(array(
            'bundle',
            'controller',
            'action',
            '__',
        ), array(
            null,
            null,
            null,
            '_',
        ), $routeName);
    }
}

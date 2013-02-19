<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route as FrameworkExtraBundleRoute;
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
 * It also parse the @Method annotation.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AnnotatedRouteControllerLoader extends AnnotationClassLoader
{
    /**
     * Configures the _controller default parameter and eventually the _method
     * requirement of a given Route instance.
     *
     * @param Route             $route  A route instance
     * @param \ReflectionClass  $class  A ReflectionClass instance
     * @param \ReflectionMethod $method A ReflectionClass method
     * @param mixed             $annot  The annotation class instance
     *
     * @throws \LogicException When the service option is specified on a method
     */
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        // controller
        $classAnnot = $this->reader->getClassAnnotation($class, $this->routeAnnotationClass);
        if ($classAnnot instanceof FrameworkExtraBundleRoute && $service = $classAnnot->getService()) {
            $route->setDefault('_controller', $service.':'.$method->getName());
        } else {
            $route->setDefault('_controller', $class->getName().'::'.$method->getName());
        }

        // requirements (@Method)
        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof Method) {
                $route->setRequirement('_method', implode('|', $configuration->getMethods()));
            } elseif ($configuration instanceof FrameworkExtraBundleRoute && $configuration->getService()) {
                throw new \LogicException('The service option can only be specified at class level.');
            }
        }
    }

    /**
     * Makes the default route name more sane by removing common keywords.
     *
     * @param  \ReflectionClass  $class  A ReflectionClass instance
     * @param  \ReflectionMethod $method A ReflectionMethod instance
     *
     * @return string The default route name
     */
    protected function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        $routeName = parent::getDefaultRouteName($class, $method);

        return preg_replace(array(
            '/(bundle|controller)_/',
            '/action(_\d+)?$/',
            '/__/'
        ), array(
            '_',
            '\\1',
            '_'
        ), $routeName);
    }
}

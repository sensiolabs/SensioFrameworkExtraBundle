<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route as FrameworkExtraBundleRoute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\RouteCollection;

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
     * Configures the _controller default parameter and eventually the HTTP method
     * requirement of a given Route instance.
     *
     * @param mixed $annot The annotation class instance
     *
     * @throws \LogicException When the service option is specified on a method
     */
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        // controller
        $classAnnot = $this->reader->getClassAnnotation($class, $this->routeAnnotationClass);
        if ($classAnnot instanceof FrameworkExtraBundleRoute && $service = $classAnnot->getService()) {
            $route->setDefault('_controller', $service.':'.$method->getName());
        } elseif ('__invoke' === $method->getName()) {
            $route->setDefault('_controller', $class->getName());
        } else {
            $route->setDefault('_controller', $class->getName().'::'.$method->getName());
        }

        // requirements (@Method)
        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof Method) {
                $route->setMethods(implode('|', $configuration->getMethods()));
            } elseif ($configuration instanceof FrameworkExtraBundleRoute && $configuration->getService()) {
                throw new \LogicException('The service option can only be specified at class level.');
            }
        }
    }

    protected function getGlobals(\ReflectionClass $class)
    {
        $globals = parent::getGlobals($class);

        foreach ($this->reader->getClassAnnotations($class) as $configuration) {
            if ($configuration instanceof Method) {
                $globals['methods'] = array_merge($globals['methods'], $configuration->getMethods());
            }
        }

        return $globals;
    }

    /**
     * Makes the default route name more sane by removing common keywords.
     *
     * @return string The default route name
     */
    protected function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        $routeName = parent::getDefaultRouteName($class, $method);

        return preg_replace(
            array('/(bundle|controller)_/', '/action(_\d+)?$/', '/__/'),
            array('_', '\\1', '_'),
            $routeName
        );
    }

    protected function addRoute(RouteCollection $collection, $annot, $globals, \ReflectionClass $class, \ReflectionMethod $method)
    {
        $name = $this->getRouteName($annot, $class, $method);

        $defaults = array_replace($globals['defaults'], $annot->getDefaults());
        foreach ($method->getParameters() as $param) {
            if (false !== strpos($globals['path'].$annot->getPath(), sprintf('{%s}', $param->getName())) && !isset($defaults[$param->getName()]) && $param->isDefaultValueAvailable()) {
                $defaults[$param->getName()] = $param->getDefaultValue();
            }
        }
        $requirements = array_replace($globals['requirements'], $annot->getRequirements());
        $options = array_replace($globals['options'], $annot->getOptions());
        $schemes = array_merge($globals['schemes'], $annot->getSchemes());
        $methods = array_merge($globals['methods'], $annot->getMethods());

        $host = $annot->getHost();
        if (null === $host) {
            $host = $globals['host'];
        }

        $condition = $annot->getCondition();
        if (null === $condition) {
            $condition = $globals['condition'];
        }

        $route = $this->createRoute($globals['path'].$annot->getPath(), $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

        $this->configureRoute($route, $class, $method, $annot);

        $collection->add($name, $route);
    }

    protected function getRouteName($annot, \ReflectionClass $class, \ReflectionMethod $method)
    {
        $name = $annot->getName();
        $defaultControllerNamePrefix = '';
        if (null === $name) {
            $name = $this->getDefaultRouteName($class, $method);
            $defaultControllerNamePrefix = preg_replace(
                array('/(bundle|controller)/', '/_+/'),
                array('_', '_'),
                strtolower(str_replace('\\', '_', $class->name))
            );
        }
        $classAnnot = $this->reader->getClassAnnotation($class, $this->routeAnnotationClass);
        if ($classAnnot instanceof FrameworkExtraBundleRoute) {
            $routeNamePrefix = $classAnnot->getName();
            if ($routeNamePrefix) {
                $name = $routeNamePrefix.'_'.str_replace($defaultControllerNamePrefix, '', $name);
            }
        }

        return $name;
    }
}

<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Routing\Loader;

use \ReflectionClass;
use \ReflectionMethod;

/**
 * Extension to the core annotation loader.
 *
 * @author Henrik Bjornskov <hb@peytz.dk>
 */
abstract class AnnotationClassLoader extends \Symfony\Component\Routing\Loader\AnnotationClassLoader
{
    /**
     * Makes the default route name more sane by removing common keywords.
     *
     * @param  ReflectionClass $class
     * @param  ReflectionMethod $method
     * @return string
     */
    public function getDefaultRouteName(ReflectionClass $class, ReflectionMethod $method)
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

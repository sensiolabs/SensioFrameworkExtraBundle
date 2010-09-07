<?php

namespace Bundle\Sensio\FrameworkExtraBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Annotation\Route as RouteAnnotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameConverter;

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

    protected function getRouteDefaults(\ReflectionClass $class, \ReflectionMethod $method, RouteAnnotation $annot)
    {
        return array(
            '_controller' => $this->converter->toShortNotation($class->getName().'::'.$method->getName())
        );
    }
}

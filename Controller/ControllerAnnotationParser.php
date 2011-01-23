<?php

namespace Bundle\Sensio\FrameworkExtraBundle\Controller;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Bundle\Sensio\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Bundle\Sensio\FrameworkExtraBundle\Configuration\AnnotationReader;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * .
 *
 * The filter method must be connected to the core.controller event.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ControllerAnnotationParser
{
    protected $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * 
     *
     * @param Event $event An Event instance
     */
    public function filter(Event $event, $controller)
    {
        if (!is_array($controller)) {
            return $controller;
        }

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        $request = $event->get('request');

        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof ConfigurationInterface) {
                $request->attributes->set('_'.$configuration->getAliasName(), $configuration);
            }
        }

        return $controller;
    }
}

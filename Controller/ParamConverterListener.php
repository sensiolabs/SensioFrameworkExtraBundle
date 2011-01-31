<?php

namespace Bundle\Sensio\FrameworkExtraBundle\Controller;

use Bundle\Sensio\FrameworkExtraBundle\Configuration\ParamConverter;
use Bundle\Sensio\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * ParamConverterListener.
 *
 * The filter method must be connected to the core.controller event.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ParamConverterListener
{
    protected $manager;

    public function __construct(ParamConverterManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * 
     *
     * @param Event $event An Event instance
     */
    public function filter(Event $event, $controller)
    {
        $request = $event->get('request');

        if ($configuration = $request->attributes->get('_converters')) {
            $this->manager->apply($request, $configuration);
        }

        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } else {
            $r = new \ReflectionFunction($controller);
        }

        // automatically apply conversion for non-configured objects
        foreach ($r->getParameters() as $param) {
            if ($param->getClass() && !$request->attributes->get($param->getName())) {
                $configuration = new ParamConverter();
                $configuration->setName($param->getName());
                $configuration->setClass($param->getClass()->getName());
                $configuration->setOptional($param->isOptional());

                $this->manager->apply($request, $configuration);
            }
        }

        return $controller;
    }
}

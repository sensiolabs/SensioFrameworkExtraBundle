<?php

namespace Bundle\Sensio\FrameworkExtraBundle\Controller;

use Bundle\Sensio\FrameworkExtraBundle\Configuration\ParamConverter;
use Bundle\Sensio\FrameworkExtraBundle\Request\Converter\ConverterManager;
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
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ParamConverterListener
{
    protected $manager;
    protected $dirs;

    public function __construct(ConverterManager $manager, $dirs)
    {
        $this->manager = $manager;
        $this->dirs = $dirs;
    }

    /**
     * Registers a core.controller listener.
     *
     * @param EventDispatcher $dispatcher An EventDispatcher instance
     * @param integer         $priority   The priority
     */
    public function register(EventDispatcher $dispatcher, $priority = 0)
    {
        $dispatcher->connect('core.controller', array($this, 'filter'), $priority);
    }

    /**
     * 
     *
     * @param Event $event An Event instance
     */
    public function filter(Event $event, $controller)
    {
        $request = $event->getParameter('request');

        if ($configuration = $request->attributes->get('_converters')) {
            $this->manager->apply($request, $configuration);
        }

        if (!is_array($controller)) {
            return $controller;
        }

        // automatically apply conversion for non-configured objects
        $r = new \ReflectionObject($controller[0]);
        foreach ($r->getMethod($controller[1])->getParameters() as $param) {
            if ($param->getClass() && !$request->attributes->get($param->getName())) {
                $configuration = new ParamConverter();
                $configuration->setName($param->getName());
                $configuration->setClass($param->getClass()->getName());

                $this->manager->apply($request, $configuration);
            }
        }

        return $controller;
    }
}

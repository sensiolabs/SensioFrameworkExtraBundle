<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * ParamConverterListener.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
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
    public function onCoreController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

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

                $configuration->setIsOptional($param->isOptional());

                $this->manager->apply($request, $configuration);
            }
        }
    }
}

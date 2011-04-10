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
 * The ParamConverterListener handles the @extra:ParamConverter annotation.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ParamConverterListener
{
    /**
     * @var Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager
     */
    protected $manager;

    /**
     * Constructor.
     *
     * @param ParamConverterManager $manager A ParamConverterManager instance
     */
    public function __construct(ParamConverterManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Modifies the ParamConverterManager instance.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();
        $configurations = array();

        if ($configuration = $request->attributes->get('_converters')) {
            $configurations = is_array($configuration) ? $configuration : array($configuration);
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

                $configurations[] = $configuration;
            }
        }

        $this->manager->apply($request, $configurations);
    }
}

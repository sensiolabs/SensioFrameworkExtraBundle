<?php

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

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
 * The ParamConverterListener handles the @ParamConverter annotation.
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
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();
        $configurations = array();

        if ($configuration = $request->attributes->get('_converters')) {
            foreach (is_array($configuration) ? $configuration : array($configuration) as $configuration) {
                $configurations[$configuration->getName()] = $configuration;
            }
        }

        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } else {
            $r = new \ReflectionFunction($controller);
        }

        // automatically apply conversion for non-configured objects
        foreach ($r->getParameters() as $param) {
            if (!$param->getClass()) {
                continue;
            }

            $name = $param->getName();

            // the parameter is already set, so disable the conversion
            if ($request->attributes->has($name)) {
                unset($configurations[$name]);
            } else {
                if (isset($configurations[$name])) {
                    $configuration = $configurations[$name];
                } else {
                    $configuration = new ParamConverter(array());
                    $configuration->setName($name);
                    $configuration->setClass($param->getClass()->getName());
                }
                $configuration->setIsOptional($param->isOptional());

                $configurations[$name] = $configuration;
            }
        }

        $this->manager->apply($request, array_values($configurations));
    }
}

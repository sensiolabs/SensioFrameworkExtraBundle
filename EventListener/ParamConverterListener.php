<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The ParamConverterListener handles the ParamConverter annotation.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ParamConverterListener implements EventSubscriberInterface
{
    /**
     * @var ParamConverterManager
     */
    protected $manager;

    protected $autoConvert;

    /**
     * Constructor.
     *
     * @param ParamConverterManager $manager     A ParamConverterManager instance
     * @param bool                  $autoConvert Auto convert non-configured objects
     */
    public function __construct(ParamConverterManager $manager, $autoConvert = true)
    {
        $this->manager = $manager;
        $this->autoConvert = $autoConvert;
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
        } elseif (is_object($controller) && is_callable($controller, '__invoke')) {
            $r = new \ReflectionMethod($controller, '__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }

        // automatically apply conversion for non-configured objects
        if ($this->autoConvert) {
            $configurations = $this->autoConfigure($r, $request, $configurations);
        }

        $this->manager->apply($request, $configurations);
    }

    private function autoConfigure(\ReflectionFunctionAbstract $r, Request $request, $configurations)
    {
        foreach ($r->getParameters() as $param) {
            if (!$param->getClass() || $param->getClass()->isInstance($request)) {
                continue;
            }

            $name = $param->getName();

            if (!isset($configurations[$name])) {
                $configuration = new ParamConverter(array());
                $configuration->setName($name);
                $configuration->setClass($param->getClass()->getName());

                $configurations[$name] = $configuration;
            } elseif (null === $configurations[$name]->getClass()) {
                $configurations[$name]->setClass($param->getClass()->getName());
            }

            $configurations[$name]->setIsOptional($param->isOptional());
        }

        return $configurations;
    }

    /**
     * Get subscribed events.
     *
     * @return array Subscribed events
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }
}

<?php

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Doctrine\Common\Util\ClassUtils;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * The ControllerListener class parses annotation blocks located in
 * controller classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ControllerListener
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $reader;

    /**
     * Constructor.
     *
     * @param Reader $reader An Reader instance
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Modifies the Request object to apply configuration information found in
     * controllers annotations like the template to render or HTTP caching
     * configuration.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
        $object    = new \ReflectionClass($className);
        $method    = $object->getMethod($controller[1]);

        $classConfigurations  = $this->getConfigurations($this->reader->getClassAnnotations($object));
        $methodConfigurations = $this->getConfigurations($this->reader->getMethodAnnotations($method));

        $configurations = array_merge($classConfigurations, $methodConfigurations);

        $request = $event->getRequest();
        foreach ($configurations as $key => $attributes) {
            if (is_array($attributes) && count($attributes) == 1) {
                $attributes = $attributes[0];
            }
            $request->attributes->set($key, $attributes);
        }
    }

    protected function getConfigurations(array $annotations)
    {
        $configurations = array();
        foreach ($annotations as $configuration) {
            if ($configuration instanceof ConfigurationInterface) {
                $configurations['_'.$configuration->getAliasName()][] = $configuration;
            }
        }

        return $configurations;
    }
}

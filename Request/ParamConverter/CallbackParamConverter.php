<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Generic Callback Param Converter
 *
 * Map a collection of callbacks or factories for classes as param converters
 *
 * @author     Ray Rehbein <mrrehbein@gmail.com>
 */
class CallbackParamConverter implements ParamConverterInterface
{
    protected $container;
    protected $serviceMap  = array();
    protected $callbackMap = array();

    public function __construct(ContainerInterface $container, array $map = array())
    {
        $this->container = $container;
        foreach ($map as $class => $serviceCallback) {
            $this->addService($class, $serviceCallback['service'], $serviceCallback['method']);
        }
    }

    /**
     * Add lazy-load callbacks / factories to the converter
     */
    public function addService($class, $service, $method)
    {
        if (!$this->container->has($service)) {
            throw new ServiceNotFoundException($service);
        }
        $this->serviceMap[$class] = array('service' => $service, 'method' => $method);
    }

    /**
     * Add callbacks / factories to the converter
     *
     * @param string $class
     * @param callable $callback
     */
    public function addCallback($class, $callback)
    {
        $this->callbackMap[$class] = $callback;
    }

    /**
     * @param string $class
     */
    protected function hasCallback($class)
    {
        return isset($this->callbackMap[$class]) || isset($this->serviceMap[$class]);
    }

    /**
     * @param string $class
     */
    protected function getCallback($class)
    {
        if (isset($this->callbackMap[$class])) {
            return $this->callbackMap[$class];
        }

        if (!isset($this->serviceMap[$class])) {
            throw new NotFoundHttpException(sprintf('%s converter not found.', $class));
        }

        $serviceEntry = $this->serviceMap[$class];
        $service      = $this->container->get($serviceEntry['service']);
        $callback     = array($service, $serviceEntry['method']);

        $this->addCallback($class, $callback);

        return $callback;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $options = $this->getOptions($configuration);
        $id      = $options['id'];

        if (!$request->attributes->has($id)) {
            return false;
        }

        $value    = $request->attributes->get($id);
        $class    = $configuration->getClass();
        $callback = $this->getCallback($class);
        $object   = call_user_func($callback, $value);

        if (null === $object && false === $configuration->isOptional()) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }

        $request->attributes->set($configuration->getName(), $object);
    }

    public function supports(ConfigurationInterface $configuration)
    {
        if (null === $this->container) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        return $this->hasCallback($configuration->getClass());
    }

    protected function getOptions(ConfigurationInterface $configuration)
    {
        return array_replace(
            array(
                'id' => 'id',
            ),
            $configuration->getOptions()
        );
    }
}

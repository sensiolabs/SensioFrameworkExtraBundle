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
 * Generic Provider Param Converter
 *
 * Map a collection of providers or factories for classes as param converters
 *
 * @author     Ray Rehbein <mrrehbein@gmail.com>
 */
class ProviderParamConverter implements ParamConverterInterface
{
    protected $container;
    protected $serviceMap = array();
    protected $providerMap = array();

    public function __construct(ContainerInterface $container, array $map = array())
    {
        $this->container = $container;
        foreach ($map as $class => $serviceCallback) {
            $this->addService($class, $serviceCallback['service'], $serviceCallback['method']);
        }
    }

    /**
     * Add lazy-load providers / factories to the converter
     */
    public function addService($class, $service, $method)
    {
        if (!$this->container->has($service)) {
            throw new ServiceNotFoundException($service);
        }
        $this->serviceMap[$class] = array('service' => $service, 'method' => $method);
    }

    /**
     * Add providers / factories to the converter
     *
     * @param string $class
     * @param callable $provider
     */
    public function addProvider($class, $provider)
    {
        $this->providerMap[$class] = $provider;
    }

    /**
     * @param string $class
     */
    protected function hasProvider($class)
    {
        return isset($this->providerMap[$class]) || isset($this->serviceMap[$class]);
    }

    /**
     * @param string $class
     */
    protected function getProvider($class)
    {
        if (isset($this->providerMap[$class])) {
            return $this->providerMap[$class];
        }

        if (!isset($this->serviceMap[$class])) {
            throw new NotFoundHttpException(sprintf('%s converter not found.', $class));
        }

        $serviceEntry = $this->serviceMap[$class];
        $service      = $this->container->get($serviceEntry['service']);
        $callback     = array($service, $serviceEntry['method']);

        $this->addProvider($class, $callback);

        return $callback;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $options = $this->getOptions($configuration);
        $id      = $options['id'];

        if (!$request->attributes->has($id)) {
            return false;
        }

        $value   = $request->attributes->get($id);
        $class   = $configuration->getClass();
        $provider = $this->getProvider($class);
        $object  = call_user_func($provider, $value);

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

        return $this->hasProvider($configuration->getClass());
    }

    protected function getOptions(ConfigurationInterface $configuration)
    {
        return array_replace(array(
            'id' => 'id',
        ), $configuration->getOptions());
    }
}

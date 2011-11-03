<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\Mapping\MappingException;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * DoctrineMongoDBConverter.
 *
 * @author     Kacper Gunia <cakper@gmail.com>
 */
class DoctrineMongoDBParamConverter implements ParamConverterInterface
{
    protected $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $class = $configuration->getClass();
        $options = $this->getOptions($configuration);

        // find by identifier?
        if (false === $object = $this->find($class, $request, $options)) {
            // find by criteria
            if (false === $object = $this->findOneBy($class, $request, $options)) {
                throw new \LogicException('Unable to guess how to get a Doctrine instance from the request information.');
            }
        }

        if (null === $object && false === $configuration->isOptional()) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }

        $request->attributes->set($configuration->getName(), $object);
    }

    protected function find($class, Request $request, $options)
    {
        if (!$request->attributes->has('id')) {
            return false;
        }
        
        return $this->container->get(sprintf('doctrine.odm.mongodb.%s_document_manager', $options['document_manager']))->find($class, $request->attributes->get('id'));
    }

    protected function findOneBy($class, Request $request, $options)
    {
        $criteria = array();
        $metadata = $this->container->get(sprintf('doctrine.odm.mongodb.%s_document_manager', $options['document_manager']))->getClassMetadata($class);
        foreach ($request->attributes->all() as $key => $value) {
            if ($metadata->hasField($key)) {
                $criteria[$key] = $value;
            }
        }

        if (!$criteria) {
            return false;
        }
        
        return $this->container->get(sprintf('doctrine.odm.mongodb.%s_document_manager', $options['document_manager']))->getRepository($class)->findOneBy($criteria);
    }

    public function supports(ConfigurationInterface $configuration)
    {
        if (null === $this->container) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }
        
        $options = $this->getOptions($configuration);

        try {
            $this->container->get(sprintf('doctrine.odm.mongodb.%s_document_manager', $options['document_manager']))->getClassMetadata($configuration->getClass());
            
            return true;
        } catch (MappingException $e) {
            return false;
        } catch (\ErrorException $e) {
            return false;
        }
    }

    protected function getOptions(ConfigurationInterface $configuration)
    {
        return array_replace(array(
            'document_manager' => 'default',
        ), $configuration->getOptions());
    }
}


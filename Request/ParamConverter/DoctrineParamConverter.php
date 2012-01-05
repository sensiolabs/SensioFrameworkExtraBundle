<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ManagerRegistry;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * DoctrineConverter.
 * 
 * The DoctrineConverter can recieve three options in the options array:
 *   entity_manager - the entity manager to user for fetching the object
 * 2. request_atttribute - the name of the attribute you should use to fetch the item (Defaults to 'id').
 * 3. object_attribute - the name of the object attribute used for fetching the object (Defaults to false - i.e. use the objects natural ID.).
 * 
 * 
 * 
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class DoctrineParamConverter implements ParamConverterInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $class = $configuration->getClass();
        $options = $this->getOptions($configuration);
        
        $attribute = (isset($options['request_attribute']) ? $options['request_attribute'] : 'id' );
        $queryAttribute = (isset($options['object_attribute']) ? $options['object_attribute'] : false );
        
        if (false === $queryAttribute) {
            // find by identifier?
            $object = $this->find($class, $request, $options, $attribute);
            if (false === $object ) {
                // find by criteria
                if (false === $object = $this->findOneBy($class, $request, $options)) {
                    throw new \LogicException('Unable to guess how to get a Doctrine instance from the request information.');
                }
            }
        } else {
            $criteria = array($queryAttribute => $request->attributes->get($attribute));
            $object = $this->registry->getRepository($class, $options['entity_manager'])->findOneBy($criteria);
        }

        if (null === $object && false === $configuration->isOptional()) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }
        $request->attributes->set($configuration->getName(), $object);

        return true;
    }

    protected function find($class, Request $request, $options, $attribute)
    {
        if (!$request->attributes->has($attribute)) {
            return false;
        }
        return $this->registry->getRepository($class, $options['entity_manager'])->find($request->attributes->get($attribute));
    }

    protected function findOneBy($class, Request $request, $options)
    {
        $criteria = array();
        $metadata = $this->registry->getManager($options['entity_manager'])->getClassMetadata($class);
        foreach ($request->attributes->all() as $key => $value) {
            if ($metadata->hasField($key)) {
                $criteria[$key] = $value;
            }
        }

        if (!$criteria) {
            return false;
        }

        return $this->registry->getRepository($class, $options['entity_manager'])->findOneBy($criteria);
    }

    public function supports(ConfigurationInterface $configuration)
    {
        if (null === $this->registry) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        $options = $this->getOptions($configuration);

        // Doctrine Entity?
        return ! $this->registry->getManager($options['entity_manager'])
                                ->getMetadataFactory()
                                ->isTransient($configuration->getClass());
    }

    protected function getOptions(ConfigurationInterface $configuration)
    {
        return array_replace(array(
            'entity_manager' => 'default',
            'request_atttribute' => 'id',
            'object_attribute' => false
        ), $configuration->getOptions());
    }
}

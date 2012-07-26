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
 * DoctrineParamConverter.
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

        // find by identifier?
        if (false === $object = $this->find($class, $request, $options)) {
            // find by criteria
            if (false === $object = $this->findOneBy($class, $request, $options)) {
                if ($configuration->isOptional()) {
                    $object = null;
                } else {
                    throw new \LogicException('Unable to guess how to get a Doctrine instance from the request information.');
                }
            }
        }

        if (null === $object && false === $configuration->isOptional()) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }

        $request->attributes->set($configuration->getName(), $object);

        return true;
    }

    protected function find($class, Request $request, $options)
    {
        if ($options['mapping'] || $options['exclude']) {
            return false;
        }

        if (!is_array($options['id']) && $request->attributes->has($options['id'])) {
            $id = $request->attributes->get($options['id']);
        } elseif (is_array($options['id'])) {
            $id = array();
            foreach ($options['id'] as $field) {
                $id[$field] = $request->attributes->get($field);
            }
        } else {
            return false;
        }

        return $this->registry->getRepository($class, $options['entity_manager'])->find($id);
    }

    protected function findOneBy($class, Request $request, $options)
    {
        if (!$options['mapping']) {
            $keys               = $request->attributes->keys();
            $options['mapping'] = $keys ? array_combine($keys, $keys) : array();
        }

        foreach ($options['exclude'] as $exclude) {
            unset($options['mapping'][$exclude]);
        }

        if (!$options['mapping']) {
            return false;
        }

        $criteria = array();
        $metadata = $this->registry->getManager($options['entity_manager'])->getClassMetadata($class);

        foreach ($options['mapping'] as $attribute => $field) {
            if ($metadata->hasField($field) || ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field))) {
                $criteria[$field] = $request->attributes->get($attribute);
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
            'entity_manager' => null,
            'exclude'        => array(),
            'mapping'        => array(),
            'id'             => 'id',
        ), $configuration->getOptions());
    }
}

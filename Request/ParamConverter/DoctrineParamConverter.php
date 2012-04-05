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
        $options = $this->getOptions($configuration);

        // find by identifier?
        if (false === $object = $this->find($configuration, $request, $options)) {
            // find by criteria
            if (false === $object = $this->findOneBy($configuration, $request, $options)) {
                throw new \LogicException('Unable to guess how to get a Doctrine instance from the request information.');
            }
        }

        if (null === $object && false === $configuration->isOptional()) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $configuration->getClass()));
        }

        $request->attributes->set($configuration->getName(), $object);

        return true;
    }

    protected function find(ConfigurationInterface $configuration, Request $request, $options)
    {
        $name = $configuration->getName();

        if ((null === $id = $request->attributes->get('id')) &&
            (null === $id = $request->attributes->get($name))) {
            return false;
        }

        $class = $configuration->getClass();

        return $this->registry->getRepository($class, $options['entity_manager'])->find($id);
    }

    protected function findOneBy(ConfigurationInterface $configuration, Request $request, $options)
    {
        $class = $configuration->getClass();
        $metadata = $this->registry->getManager($options['entity_manager'])->getClassMetadata($class);
        $criteria = array();
        $parameter_prefix = $configuration->getName() . '_';

        foreach ($request->attributes->all() as $key => $value) {
            if (!$metadata->hasField($key) && !$metadata->hasAssociation($key)) {
                if (false === strpos($key, $parameter_prefix)) {
                    continue;
                }

                $key = substr($key, strlen($parameter_prefix));

                if (!$metadata->hasField($key) && !$metadata->hasAssociation($key)) {
                    continue;
                }
            }

            $criteria[$key] = $value;
        }

        if (empty($criteria)) {
            return false;
        }

        return call_user_func(array($this->registry->getRepository($class, $options['entity_manager']), $options['method']), $criteria);
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
            'method' => 'findOneBy',
        ), $configuration->getOptions());
    }
}

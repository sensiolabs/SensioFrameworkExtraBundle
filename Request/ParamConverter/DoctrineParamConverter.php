<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\MappingException;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;

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
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class DoctrineParamConverter implements ParamConverterInterface
{
    protected $manager;

    public function __construct($manager = null)
    {
        if (is_null($manager)) {
            return;
        }

        if (false === (($manager instanceof EntityManager) || ($manager instanceof DocumentManager))) {
            throw new \InvalidArgumentException('$manager must be an instance of Doctrine\ORM\EntityManager or Doctrine\ODM\MongoDB\DocumentManager.');
        }

        $this->manager = $manager;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $class = $configuration->getClass();

        // find by identifier?
        if (false === $object = $this->find($class, $request)) {
            // find by criteria
            if (false === $object = $this->findOneBy($class, $request)) {
                throw new \LogicException('Unable to guess how to get a Doctrine instance from the request information.');
            }
        }

        if (null === $object) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }

        $request->attributes->set($configuration->getName(), $object);
    }

    protected function find($class, Request $request)
    {
        if (!$request->attributes->has('id')) {
            return false;
        }

        return $this->manager->getRepository($class)->find($request->attributes->get('id'));
    }

    protected function findOneBy($class, Request $request)
    {
        $criteria = array();
        $metadata = $this->manager->getClassMetadata($class);
        foreach ($request->attributes->all() as $key => $value) {
            if ($metadata->hasField($key)) {
                $criteria[$key] = $value;
            }
        }

        if (!$criteria) {
            return false;
        }

        return $this->manager->getRepository($class)->findOneBy($criteria);
    }

    public function supports(ConfigurationInterface $configuration)
    {
        if (null === $this->manager) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        // Doctrine Entity?
        try {
            $this->manager->getClassMetadata($configuration->getClass());

            return true;
        } catch (MappingException $e) {
            return false;
        } catch (MongoDBException $e) {
            return false;
        }
    }
}

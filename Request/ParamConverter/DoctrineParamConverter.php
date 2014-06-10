<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;

/**
 * DoctrineParamConverter.
 *
 * @author Fabien Potencier <fabien@symfony.com>
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

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException       When unable to guess how to get a Doctrine instance from the request information
     * @throws NotFoundHttpException When object not found
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name    = $configuration->getName();
        $class   = $configuration->getClass();
        $options = $this->getOptions($configuration);

        if (null === $request->attributes->get($name, false)) {
            $configuration->setIsOptional(true);
        }

        // find by identifier?
        if (false === $object = $this->find($class, $request, $options, $name)) {
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

        $request->attributes->set($name, $object);

        return true;
    }

    protected function find($class, Request $request, $options, $name)
    {
        if ($options['mapping'] || $options['exclude']) {
            return false;
        }

        $id = $this->getIdentifier($request, $options, $name);

        if (false === $id || null === $id) {
            return false;
        }

        if (isset($options['repository_method'])) {
            $method = $options['repository_method'];
        } else {
            $method = 'find';
        }

        try {
            return $this->getManager($options['entity_manager'], $class)->getRepository($class)->$method($id);
        } catch (NoResultException $e) {
            return null;
        }
    }

    protected function getIdentifier(Request $request, $options, $name)
    {
        if (isset($options['id'])) {
            if (!is_array($options['id'])) {
                $name = $options['id'];
            } elseif (is_array($options['id'])) {
                $id = array();
                foreach ($options['id'] as $field) {
                    $id[$field] = $request->attributes->get($field);
                }

                return $id;
            }
        }

        if ($request->attributes->has($name)) {
            return $request->attributes->get($name);
        }

        if ($request->attributes->has('id') && !isset($options['id'])) {
            return $request->attributes->get('id');
        }

        return false;
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

        // if a specific id has been defined in the options and there is no corresponding attribute
        // return false in order to avoid a fallback to the id which might be of another object
        if (isset($options['id']) && null === $request->attributes->get($options['id'])) {
            return false;
        }

        $criteria = array();
        $em = $this->getManager($options['entity_manager'], $class);
        $metadata = $em->getClassMetadata($class);

        foreach ($options['mapping'] as $attribute => $field) {
            if ($metadata->hasField($field) || ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field))) {
                $criteria[$field] = $request->attributes->get($attribute);
            }
        }

        if ($options['strip_null']) {
            $criteria = array_filter($criteria, function ($value) { return !is_null($value); });
        }

        if (!$criteria) {
            return false;
        }

        if (isset($options['repository_method'])) {
            $method = $options['repository_method'];
        } else {
            $method = 'findOneBy';
        }

        try {
            return $em->getRepository($class)->$method($criteria);
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        // if there is no manager, this means that only Doctrine DBAL is configured
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        $options = $this->getOptions($configuration);

        // Doctrine Entity?
        $em = $this->getManager($options['entity_manager'], $configuration->getClass());
        if (null === $em) {
            return false;
        }

        return ! $em->getMetadataFactory()->isTransient($configuration->getClass());
    }

    protected function getOptions(ParamConverter $configuration)
    {
        return array_replace(array(
            'entity_manager' => null,
            'exclude'        => array(),
            'mapping'        => array(),
            'strip_null'     => false,
        ), $configuration->getOptions());
    }

    private function getManager($name, $class)
    {
        if (null === $name) {
            return $this->registry->getManagerForClass($class);
        }

        return $this->registry->getManager($name);
    }
}

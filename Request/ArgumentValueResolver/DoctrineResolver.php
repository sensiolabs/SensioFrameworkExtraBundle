<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentValueResolver;

use Sensio\Bundle\FrameworkExtraBundle\Exception\LogicException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineResolver implements ArgumentValueOptionsInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var ExpressionLanguage
     */
    private $language;

    private $enabled;

    public function __construct(ManagerRegistry $registry = null, ExpressionLanguage $expressionLanguage = null)
    {
        $this->registry = $registry;
        $this->language = $expressionLanguage;

        // if there is no manager, this means that only Doctrine DBAL is configured
        $this->enabled = null !== $this->registry && count($this->registry->getManagers());
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (!$this->enabled || null === $argument->getType()) {
            return false;
        }

        $options = $request->attributes->get($argument->getName().'_options');

        // Doctrine Entity?
        $em = $this->getManager(isset($options['entity_manager']) ? $options['entity_manager'] : null, $argument->getType());
        if (null === $em) {
            return false;
        }

        return !$em->getMetadataFactory()->isTransient($argument->getType());
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'entity_manager' => null,
            'exclude' => array(),
            'mapping' => array(),
            'strip_null' => false,
            'expr' => null,
            'id' => null,
        ));

        $resolver->setAllowedTypes('entity_manager', array('string', 'null'));
        $resolver->setAllowedTypes('expr', array('string', 'null'));
        $resolver->setAllowedTypes('id', array('string', 'null'));
        $resolver->setAllowedTypes('strip_null', 'bool');
        $resolver->setAllowedTypes('exclude', 'array');
        $resolver->setAllowedTypes('mapping', 'array');
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException        When unable to guess how to get a Doctrine instance from the request information
     * @throws NotFoundHttpException When object not found
     */
    public function resolve(Request $request, ArgumentMetadata $metadata)
    {
        $name = $metadata->getName();
        $class = $metadata->getType();
        $options = $request->attributes->get($name.'_options');
        $errorMessage = null;
        if ($expr = $options['expr']) {
            $object = $this->findViaExpression($class, $request, $expr, $options);

            if (null === $object) {
                $errorMessage = sprintf('The expression "%s" returned null', $expr);
            }

            // find by identifier?
        } elseif (false === $object = $this->find($class, $request, $options, $name)) {
            // find by criteria
// FIXME: to be done elsewhere for expr for instance
$method = $method->isArray() ? 'findBy' : 'findOneBy';
            if (false === $object = $this->findOneBy($class, $request, $options)) {
                if ($metadata->hasDefaultValue() || $metadata->isNullable()) {
                    $object = null;
                } else {
                    throw new LogicException('Unable to guess how to get a Doctrine instance from the request information.');
                }
            }
        }

        if (null === $object && (!$metadata->hasDefaultValue() && !$metadata->isNullable())) {
            $message = sprintf('%s object not found by the @Arg annotation.', $class);
            if ($errorMessage) {
                $message .= ' '.$errorMessage;
            }
            throw new NotFoundHttpException($message);
        }

        yield $object;
    }

    private function find($class, Request $request, $options, $name)
    {
        if ($options['mapping'] || $options['exclude']) {
            return false;
        }

        $id = $this->getIdentifier($request, $options, $name);

        if (false === $id || null === $id) {
            return false;
        }

        $method = 'find';

        try {
            return $this->getManager($options['entity_manager'], $class)->getRepository($class)->$method($id);
        } catch (NoResultException $e) {
            return;
        }
    }

    private function getIdentifier(Request $request, $options, $name)
    {
        if (null !== $options['id']) {
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

        if ($request->attributes->has('id') && !$options['id']) {
            return $request->attributes->get('id');
        }

        return false;
    }

    private function findOneBy($class, Request $request, $options)
    {
        if (!$options['mapping']) {
            $keys = $request->attributes->keys();
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
        if ($options['id'] && null === $request->attributes->get($options['id'])) {
            return false;
        }

        $criteria = array();
        $em = $this->getManager($options['entity_manager'], $class);
        $metadata = $em->getClassMetadata($class);

        foreach ($options['mapping'] as $attribute => $field) {
            if ($metadata->hasField($field)
                || ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field))
            ) {
                $criteria[$field] = $request->attributes->get($attribute);
            }
        }

        if ($options['strip_null']) {
            $criteria = array_filter($criteria, function ($value) { return !is_null($value); });
        }

        if (!$criteria) {
            return false;
        }

        try {
            return $em->getRepository($class)->findOneBy($criteria);
        } catch (NoResultException $e) {
            return;
        }
    }

    private function findDataByMapMethodSignature($em, $class, $repositoryMethod, $criteria)
    {
        $arguments = array();
        $repository = $em->getRepository($class);
        $ref = new \ReflectionMethod($repository, $repositoryMethod);
        foreach ($ref->getParameters() as $parameter) {
            if (array_key_exists($parameter->name, $criteria)) {
                $arguments[] = $criteria[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
            } else {
                throw new \InvalidArgumentException(sprintf('Repository method "%s::%s" requires that you provide a value for the "$%s" argument.', get_class($repository), $repositoryMethod, $parameter->name));
            }
        }

        return $ref->invokeArgs($repository, $arguments);
    }

    private function findViaExpression($class, Request $request, $expression, $options)
    {
        if (null === $this->language) {
            throw new LogicException(sprintf('To use the @Arg tag with the "expr" option, you need install the ExpressionLanguage component.'));
        }

        $repository = $this->getManager($options['entity_manager'], $class)->getRepository($class);
        $variables = array_merge($request->attributes->all(), array('repository' => $repository));

        try {
            return $this->language->evaluate($expression, $variables);
        } catch (NoResultException $e) {
            return;
        } catch (SyntaxError $e) {
            throw new LogicException(sprintf('Error parsing expression -- %s -- (%s)', $expression, $e->getMessage()), 0, $e);
        }
    }

    private function getManager($name, $class)
    {
        if (null === $name) {
            return $this->registry->getManagerForClass($class);
        }

        return $this->registry->getManager($name);
    }
}

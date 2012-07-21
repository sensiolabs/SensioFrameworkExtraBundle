<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Converts arrays into objects by mapping keys onto
 * constructor arguments. By restricing access to constructor
 * parameters only, security problems can be avoided and a central
 * location for filtering input data is available.
 *
 * Data is only ever retrieved from the query part of the request,
 * use the form framework for transorming POST data into objects.
 *
 * Converter can be used to generate criteria/filtering struct objects
 * that are passed to the model layer.
 *
 * Conversion works recursivly and with a special case for DateTime objects.
 *
 * Important note: This converter has to run AFTER any persistent object
 * converter such as the DoctrineParamConverter, so that data from the database
 * is preferred over user input data. Otherwise attackers could snuck in
 * objects to operate on. This is why only constructor arguments are mapped.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ObjectParamConverter implements ParamConverterInterface
{
    private $validator;

    public function __construct(ValidatorInterface $validator = null)
    {
        $this->validator = $validator;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $param  = $configuration->getName();
        $method = $request->getMethod();

        if ($request->query->has($param) && !$request->request->has($param)) {
            $data = $request->query->get($param, array());
        } else {
            $data = array();
        }

        $class  = $configuration->getClass();
        $object = $this->convertClass($class, $data);

        if ($this->validator) {
            $options = $configuration->getOptions();
            $groups  = isset($options['validation_groups'])
                ? (array)$options['validation_groups']
                : null;

            $cvl = $this->validator->validate($object, $groups);

            if (count($cvl) > 0) {
                throw new HttpException(400, (string)$cvl);
            }
        }

        $request->attributes->set($param, $object);
    }

    private function convertClass($class, $data)
    {
        $reflClass   = new \ReflectionClass($class);
        $constructor = $reflClass->getConstructor();
        $args        = $this->convertClassArguments($constructor, $data);

        return $reflClass->newInstanceArgs($args);
    }

    private function convertClassArguments($constructor, $data)
    {
        $args = array();

        if ( ! $constructor) {
            return array();
        }

        if (is_scalar($data)) {
            return array($data);
        }

        foreach ($constructor->getParameters() as $parameter) {
            $argValue      = null;
            $parameterName = $parameter->getName();

            if (isset($data[$parameterName])) {
                $argValue = $data[$parameterName];

                if ($parameter->getClass()) {
                    if ($parameter->getClass()->getName() == "DateTime") {
                        $argValue = new \DateTime($argValue);
                    } else if (is_array($argValue)) {
                        $argValue = $this->convertClass($parameter->getClass()->getName(), $argValue);
                    }
                }

            } else if ($parameter->isOptional()) {
                $argValue = $parameter->getDefaultValue();
            } else {
                throw new HttpException(400, "Missing parameter '" . $parameterName . "'");
            }

            $args[] = $argValue;
        }

        return $args;
    }

    public function supports(ConfigurationInterface $configuration)
    {
        return $configuration->getClass() !== null;
    }
}


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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DateTime;

/**
 * Convert DateTime instances from request attribute variable.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class DateTimeParamConverter implements ParamConverterInterface
{
    /**
     * @{inheritdoc}
     * 
     * @throws NotFoundHttpException When invalid date given
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $param = $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $options = $configuration->getOptions();
        $value   = $request->attributes->get($param);

        $date = isset($options['format'])
            ? DateTime::createFromFormat($options['format'], $value)
            : new DateTime($value);

        if (!$date) {
            throw new NotFoundHttpException('Invalid date given.');
        }

        $request->attributes->set($param, $date);

        return true;
    }

    /**
     * @{inheritdoc}
     */
    public function supports(ConfigurationInterface $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return "DateTime" === $configuration->getClass();
    }
}

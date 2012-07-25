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
use DateTime;

/**
 * Convert DateTime instances from request variables.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class DateTimeParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $param = $configuration->getName();

        if (!$request->get($param)) {
            return false;
        }

        $options = $configuration->getOptions();

        if (isset($options['format'])) {
            $date = DateTime::createFromFormat($options['format'], $request->get($param));
        } else {
            $date = new DateTime($request->get($param));
        }

        $request->attributes->set($param, $date);

        return true;
    }

    public function supports(ConfigurationInterface $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return "DateTime" === $configuration->getClass();
    }
}


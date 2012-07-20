<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
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
 * Convert DateTime instances from request variables.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class DateTimeParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $request->attributes->set(
            $configuration->getName(),
            new \DateTime($request->get($configuration->getName()))
        );
    }

    public function supports(ConfigurationInterface $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return "DateTime" === $configuration->getClass() ||
               is_suclass_of($configuration->getClass(), "DateTime");
    }
}


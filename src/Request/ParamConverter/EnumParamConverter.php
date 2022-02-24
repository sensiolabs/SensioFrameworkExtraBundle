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

use BackedEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class EnumParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();

        if (!$request->attributes->has($name)) {
            return false;
        }

        $value = $request->attributes->get($name);

        if (!$value && $configuration->isOptional()) {
            $request->attributes->set($name, null);

            return true;
        }

        /** @var class-string<BackedEnum> $enumClass */
        $enumClass = $configuration->getClass();
        $enum = $enumClass::from($value);

        $request->attributes->set($name, $enum);

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return enum_exists($configuration->getClass());
    }
}

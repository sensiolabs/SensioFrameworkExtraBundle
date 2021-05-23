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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Convert DateInterval instances from request attribute variable.
 *
 * @author Laurent Georget <laurent.georget@meteo-concept.fr>
 */
class DateIntervalParamConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws NotFoundHttpException When invalid date given
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $param = $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $options = $configuration->getOptions();
        $value = $request->attributes->get($param);

        if (!$value && $configuration->isOptional()) {
            $request->attributes->set($param, null);

            return true;
        }

        $class = $configuration->getClass();

        $interval = false;
        try {
            // Attempt first to parse the param as a ISO8601 date interval.
            $interval = new $class($value);
        } catch (\Exception $e) {
            // Didn't quite work, let's try to see if it's a valid date string.
            // The function first passes the value to strtotime() because
            // createFromDateString() can sometimes throw when trying to parse
            // some element of the value as a timezone only to then fail to
            // find it in the tzdata database.
            if (false !== strtotime($value)) {
                $interval = $class::createFromDateString($value);
            }
            if (false === $interval) {
                throw new NotFoundHttpException(sprintf('Invalid date interval given for parameter "%s".', $param));
            }
        }

        $request->attributes->set($param, $interval);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return \DateInterval::class === $configuration->getClass() ||
               is_subclass_of($configuration->getClass(), \DateInterval::class);
    }
}

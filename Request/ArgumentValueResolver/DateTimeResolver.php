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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Injects DateTimeInterface instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class DateTimeResolver implements ArgumentValueOptionsInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return 'DateTime' === $argument->getType() || is_subclass_of($argument->getType(), PHP_VERSION_ID < 50500 ? 'DateTime' : 'DateTimeInterface');
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'format' => null,
        ));
        $resolver->setAllowedTypes('format', array('string', 'null'));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $value = $request->attributes->get($argument->getName());
        $class = $argument->getType();
        $options = $request->attributes->get($argument->getName().'_options');

        if (null === $value && $argument->hasDefaultValue()) {
            yield $argument->getDefaultValue();
        } elseif (null === $value && $argument->isNullable()) {
            yield null;
        } else {
            if ($options['format']) {
                $date = $class::createFromFormat($options['format'], $value);
            } else {
                $date = strtotime($value);
                if (false !== $date) {
                    $date = new $class($value);
                }
            }

            if (!$date) {
                throw new NotFoundHttpException(sprintf('Invalid date given for argument "%s".', $argument->getName()));
            }

            yield $date;
        }
    }
}

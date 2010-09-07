<?php

namespace Bundle\Sensio\FrameworkExtraBundle\Request\Converter;

use Symfony\Component\HttpFoundation\Request;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * 
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ConverterManager
{
    protected $converters;

    public function __construct(array $converters = array())
    {
        $this->converters = array();
        foreach ($converters as $converter) {
            $this->addConverter($converter);
        }
    }

    public function apply(Request $request, $configurations)
    {
        if (is_object($configurations)) {
            $configurations = array($configurations);
        }

        $converted = false;
        foreach ($configurations as $configuration) {
            foreach ($this->converters as $converter) {
                if ($converter->supports($configuration)) {
                    $converted = true;
                    $converter->apply($request, $configuration);
                }
            }
        }

        if (false === $converted) {
            throw new \InvalidArgumentException(sprintf('Unable to convert @ParamConverter configuration "%s".', $configuration));
        }
    }

    public function addConverter(ConverterInterface $converter)
    {
        $this->converters[] = $converter;
    }
}

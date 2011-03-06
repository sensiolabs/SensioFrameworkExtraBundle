<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

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
 * 
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class ParamConverterManager
{
    protected $converters = array();

    public function apply(Request $request, $configurations)
    {
        if (is_object($configurations)) {
            $configurations = array($configurations);
        }

        foreach ($configurations as $configuration) {
            foreach ($this->all() as $converter) {
                if ($converter->supports($configuration)) {
                    $converter->apply($request, $configuration);
                }
            }
        }
   }

   /**
    * Adds a parameter converter.
    *
    * @param ParamConverterInterface $converter A ParamConverterInterface instance
    * @param integer                 $priority  The priority (between -10 and 10)
    */
    public function add(ParamConverterInterface $converter, $priority = 0)
    {
       if (!isset($this->converters[$priority])) {
           $this->converters[$priority] = array();
       }

       $this->converters[$priority][] = $converter;
    }

   /**
    * Returns all registered param converters.
    *
    * @return array An array of param converters
    */
   public function all()
   {
       krsort($this->converters);

       $converters = array();
       foreach ($this->converters as $all) {
           $converters = array_merge($converters, $all);
       }

       return $converters;
   }
}

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
 * Managers converters. 
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class ParamConverterManager
{
    /**
     * @var array
     */
    protected $converters = array();

    /**
     * Applies all converters to the passed configurations and stops when a 
     * converter is applied it will move on to the next configuration and so on.
     *
     * @param Request $request
     * @param array|object $configurations
     */
    public function apply(Request $request, $configurations)
    {
        if (is_object($configurations)) {
            $configurations = array($configurations);
        }

        foreach ($configurations as $configuration) {
            foreach ($this->all() as $converter) {
                if ($converter->supports($configuration)) {
                    if ($request->attributes->has($configuration->getName())) {
                        continue 2;
                    }

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

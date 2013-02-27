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
 * Converts request parameters to objects and stores them as request
 * attributes, so they can be injected as controller method arguments.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ParamConverterInterface
{
    /**
     * Stores the object in the request.
     * 
     * @param Request                $request       The request
     * @param ConfigurationInterface $configuration Contains the name, class and options of the object
     * 
     * @return boolean True if the object has been successfully set, else false
     */
    function apply(Request $request, ConfigurationInterface $configuration);

    /**
     * Checks if the object is supported.
     * 
     * @param ConfigurationInterface $configuration Should be an instance of ParamConverter
     * 
     * @return boolean True if the object is supported, else false
     */
    function supports(ConfigurationInterface $configuration);
}

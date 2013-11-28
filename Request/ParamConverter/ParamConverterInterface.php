<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @param Request        $request       The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return boolean True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration);

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return boolean True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration);
}

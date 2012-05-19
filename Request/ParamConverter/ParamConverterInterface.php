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
 *
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
interface ParamConverterInterface
{
    function apply(Request $request, ConfigurationInterface $configuration);

    function supports(ConfigurationInterface $configuration);
}

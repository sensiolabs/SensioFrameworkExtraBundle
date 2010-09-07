<?php

namespace Bundle\Sensio\FrameworkExtraBundle\Request\Converter;

use Bundle\Sensio\FrameworkExtraBundle\Configuration\ConfigurationInterface;
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
interface ConverterInterface
{
    function apply(Request $request, ConfigurationInterface $configuration);

    function supports(ConfigurationInterface $configuration);
}

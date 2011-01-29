<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sensio\FrameworkExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged request.param_converter services to converter.manager service
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class AddParamConverterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('converter.manager')) {
            return;
        }

        $converters = array();
        foreach ($container->findTaggedServiceIds('request.param_converter') as $id => $attributes) {
            $converters[] = new Reference($id);
        }

        $container->getDefinition('converter.manager')->setArgument(0, $converters);
    }
}

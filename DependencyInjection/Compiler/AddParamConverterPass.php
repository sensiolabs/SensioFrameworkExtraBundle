<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged request.param_converter services to converter.manager service
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AddParamConverterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('converter.manager')) {
            return;
        }

        $definition = $container->getDefinition('converter.manager');
        foreach ($container->findTaggedServiceIds('request.param_converter') as $id => $attributes) {
            $definition->addMethodCall('add', array(new Reference($id), isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0));
        }
    }
}

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
 * Adds tagged request.param_converter.provider services to converter.provider service
 *
 * @author Ray Rehbein <mrrehbein@gmail.com>
 */
class AddProviderParamConverterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('sensio_framework_extra.converter.provider')) {
            return;
        }

        $definition = $container->getDefinition('sensio_framework_extra.converter.provider');
        foreach ($container->findTaggedServiceIds('request.param_converter.provider') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $definition->addMethodCall(
                    'addService',
                    array(
                        $attribute['class'], $id, $attribute['method']
                    )
                );
            }
        }
    }
}

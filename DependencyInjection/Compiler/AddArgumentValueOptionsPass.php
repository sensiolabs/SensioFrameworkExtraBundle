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
 * Adds tagged controller.argument_value_options services to the argument_options.listener service.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AddArgumentValueOptionsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('sensio_framework_extra.argument_options.listener')) {
            return;
        }

        $all = array();
        foreach ($container->findTaggedServiceIds('controller.argument_value_options') as $id => $optionResolvers) {
            foreach ($optionResolvers as $optionResolver) {
                $all[] = new Reference($id);
            }
        }

        $container->getDefinition('sensio_framework_extra.argument_options.listener')->replaceArgument(2, $all);
    }
}

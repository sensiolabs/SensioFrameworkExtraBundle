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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class LegacyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('sensio_framework_extra.security.listener')) {
            return;
        }

        $definition = $container->getDefinition('sensio_framework_extra.security.listener');

        if ($container->hasDefinition('security.token_storage')) {
            $definition->replaceArgument(0, null);
        }
    }
}

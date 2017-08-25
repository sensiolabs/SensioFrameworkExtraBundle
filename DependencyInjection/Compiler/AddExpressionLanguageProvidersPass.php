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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddExpressionLanguageProvidersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage')) {
            $definition = $container->findDefinition('Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage');
            foreach ($container->findTaggedServiceIds('security.expression_language_provider') as $id => $attributes) {
                $definition->addMethodCall('registerProvider', array(new Reference($id)));
            }
        }
    }
}

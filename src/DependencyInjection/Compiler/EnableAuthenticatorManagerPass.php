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

/**
 * Optimizes the container by removing unneeded listeners.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class EnableAuthenticatorManagerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {          
        if(!$container->has('sensio_framework_extra.security.listener')){
            return;
        }
        
        if(!$container->hasExtension('security')){
            return;
        }
        
        $securityConfig = $container->getExtensionConfig('security')[0];
                
        if (array_key_exists('enable_authenticator_manager', $securityConfig)) {
            if($securityConfig['enable_authenticator_manager']){
                $container->getDefinition('sensio_framework_extra.security.listener')->setArgument('$useNewAuthSystem', true);
            }
        }
    }
}

<?php

namespace Sensio\Bundle\FrameworkExtraBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddExpressionLanguageProvidersPass;
use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddParamConverterPass;
use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\LegacyPass;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SensioFrameworkExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddParamConverterPass());
        $container->addCompilerPass(new LegacyPass());
        $container->addCompilerPass(new AddExpressionLanguageProvidersPass());
    }
}

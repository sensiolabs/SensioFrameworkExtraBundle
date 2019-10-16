<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle;

use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddExpressionLanguageProvidersPass;
use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddParamConverterPass;
use Sensio\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\OptimizerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SensioFrameworkExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddParamConverterPass());
        $container->addCompilerPass(new OptimizerPass());
        $container->addCompilerPass(new AddExpressionLanguageProvidersPass());
    }
}

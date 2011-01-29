<?php

namespace Bundle\Sensio\FrameworkExtraBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Bundle\Sensio\FrameworkExtraBundle\DependencyInjection\Compiler\AddParamConverterPass;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * SensioFrameworkExtraBundle.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class SensioFrameworkExtraBundle extends Bundle
{
    public function registerExtensions(ContainerBuilder $container)
    {
        parent::registerExtensions($container);

        $container->addCompilerPass(new AddParamConverterPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return strtr(__DIR__, '\\', '/');
    }
}

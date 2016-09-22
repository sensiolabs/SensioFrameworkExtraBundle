<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentValueResolver;

use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ArgumentValueOptionsInterface extends ArgumentValueResolverInterface
{
    /**
     * Configures available options on the resolver.
     */
    public function configure(OptionsResolver $resolver);
}

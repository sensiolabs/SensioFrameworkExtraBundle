<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ResolvedOptionsInterface.
 *
 * @author Sofiane HADDAG <sofiane.haddag@yahoo.fr>
 */
interface ResolvedOptionsInterface
{
    /**
     * Configure the Options Resolver
     *
     * @param OptionsResolver $resolver The OptionsResolver instance
     */
    public function configureResolver(OptionsResolver $resolver);
}

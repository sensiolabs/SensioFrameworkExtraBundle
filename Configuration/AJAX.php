<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The AJAX Class handles the @AJAX annotation parts.
 * 
 * @author Víctor Marqués <victmarqm@gmail.com>
 * @Annotation
 */
class AJAX extends ConfigurationAnnotation
{
    public function getAliasName()
    {
        return 'ajax';
    }
}
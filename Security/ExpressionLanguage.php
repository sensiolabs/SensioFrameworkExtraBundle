<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Security;

use Symfony\Component\Security\Core\Authorization\ExpressionLanguage as BaseExpressionLanguage;

/**
 * Adds some function to the default Symfony Security ExpressionLanguage.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExpressionLanguage extends BaseExpressionLanguage
{
    protected function registerFunctions()
    {
        parent::registerFunctions();

        $this->register('is_granted', function ($attributes, $object = null) {
            return '$security_context->isGranted($attributes, $object)';
        }, function (array $variables, $attributes, $object = null) {
            return $variables['security_context']->isGranted($attributes, $object);
        });
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sensio\Bundle\FrameworkExtraBundle\EventListener\ContentSafeListener;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
        ->set('sensio_framework_extra.contentsafe.listener', ContentSafeListener::class)
    ;
};

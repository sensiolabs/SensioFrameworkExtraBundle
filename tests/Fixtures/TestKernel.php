<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Used for functional tests.
 */
class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Tests\Fixtures\FooBundle\FooBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
        $loader->load(__DIR__.'/config/nullable_type/config.yml');

        if (self::MAJOR_VERSION >= 5) {
            $loader->load(__DIR__.'/config/config_sf5.yml');
        } else {
            $loader->load(__DIR__.'/config/config_sf4.yml');
        }
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}

class_alias('Tests\Fixtures\TestKernel', 'TestKernel');

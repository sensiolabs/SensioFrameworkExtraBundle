<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Config;

use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Config\ResourceCheckerInterface;
use Symfony\Component\Routing\Loader\AnnotationClassLoader;

class AnnotatedRoutingResourceChecker implements ResourceCheckerInterface
{
    private $annotationClassLoader;

    public function __construct(AnnotationClassLoader $annotationClassLoader)
    {
        $this->annotationClassLoader = $annotationClassLoader;
    }

    public function supports(ResourceInterface $metadata)
    {
        return $metadata instanceof AnnotatedRoutingResource;
    }

    /**
     * @param AnnotatedRoutingResource $resource
     * @param int $timestamp
     * @return bool
     */
    public function isFresh(ResourceInterface $resource, $timestamp)
    {
        if (!file_exists($resource->getFilePath())) {
            return false;
        }

        // has the file *not* been modified? Definitely fresh
        if (@filemtime($resource->getFilePath()) <= $timestamp) {
            return true;
        }

        try {
            $reflectionClass = new \ReflectionClass($resource->getClass());
        } catch (\ReflectionException $e) {
            // the class does not exist anymore!
            return false;
        }

        return (array) $resource === (array) $this->annotationClassLoader->createResourceForClass($reflectionClass);
    }

}
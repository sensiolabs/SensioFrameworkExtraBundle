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

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Config\ResourceCheckerInterface;

class AnnotatedRoutingResourceChecker implements ResourceCheckerInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
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

        $newResource = new AnnotatedRoutingResource($reflectionClass, $this->reader);

        return (array) $resource === (array) $newResource;
    }

}
<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Controller;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\AnnotationReader;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * The ControllerAnnotationParser class parses annotation blocks located in 
 * controller classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ControllerAnnotationParser
{
    /**
     * @var Sensio\Bundle\FrameworkExtraBundle\Configuration\AnnotationReader
     */
    protected $reader;

    /**
     * Constructor.
     *
     * @param AnnotationReader $reader An AnnotationReader instance
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Modifies the Request object to apply configuration information found in
     * controllers annotations like the template to render or HTTP caching 
     * configuration.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        $request = $event->getRequest();
        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof ConfigurationInterface) {
                $request->attributes->set('_'.$configuration->getAliasName(), $configuration);
            }
        }
    }
}

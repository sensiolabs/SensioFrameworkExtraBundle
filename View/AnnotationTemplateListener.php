<?php

namespace Sensio\Bundle\FrameworkExtraBundle\View;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class AnnotationTemplateListener
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 
     *
     * @param Event $event An Event instance
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_template')) {
            return;
        }

        if (!$configuration->getTemplate()) {
            $configuration->setTemplate($this->guessTemplateName($controller, $request));
        }

        $request->attributes->set('_template', $configuration->getTemplate());
        $request->attributes->set('_template_vars', $configuration->getVars());

        // all controller method arguments
        if (!$configuration->getVars()) {
            $r = new \ReflectionObject($controller[0]);

            $vars = array();
            foreach ($r->getMethod($controller[1])->getParameters() as $param) {
                $vars[] = $param->getName();
            }

            $request->attributes->set('_template_default_vars', $vars);
        }
    }

    /**
     * 
     *
     * @param Event $event An Event instance
     */
    public function onCoreView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $parameters = $event->getControllerResult();

        if (null === $parameters) {
            if (!$vars = $request->attributes->get('_template_vars')) {
                if (!$vars = $request->attributes->get('_template_default_vars')) {
                    return;
                }
            }

            $parameters = array();
            foreach ($vars as $var) {
                $parameters[$var] = $request->attributes->get($var);
            }
        }

        if (!is_array($parameters)) {
            return $parameters;
        }

        if (!$template = $request->attributes->get('_template')) {
            return $parameters;
        }

        $event->setResponse(new Response($this->container->get('templating')->render($template, $parameters)));
    }

    protected function guessTemplateName($controller, Request $request)
    {
        if (!preg_match('/Controller\\\(.*)Controller$/', get_class($controller[0]), $match)) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (it does not end with Controller)', get_class($controller[0])));
        }

        $bundle = $this->getBundleForClass(get_class($controller[0]));

        $name = $match[1].':'.substr($controller[1], 0, -6);

        return $bundle->getName().':'.$name.'.'.$request->getRequestFormat().'.twig';
    }

    protected function getBundleForClass($class)
    {
        $namespace = strtr(dirname(strtr($class, '\\', '/')), '/', '\\');
        foreach ($this->container->get('kernel')->getBundles() as $bundle) {
            if (0 === strpos($namespace, $bundle->getNamespace())) {
                return $bundle;
            }
        }

        throw new \InvalidArgumentException(sprintf('The "%s" class does not belong to a registered bundle.', $class));
    }
}

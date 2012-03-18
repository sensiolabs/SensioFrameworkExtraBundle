<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Templating;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * The TemplateGuesser class handles the guessing of template name based on controller
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class TemplateGuesser
{
    /**
     * @var Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
    
    /**
     * Guesses and returns the template name to render based on the controller
     * and action names.
     *
     * @param array $controller An array storing the controller object and action method
     * @param Request $request A Request instance
     * @param string $engine
     * @return TemplateReference template reference
     * @throws \InvalidArgumentException
     */
    public function guessTemplateName($controller, Request $request, $engine = 'twig')
    {
        if (!preg_match('/Controller\\\(.+)Controller$/', get_class($controller[0]), $matchController)) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (it must be in a "Controller" sub-namespace and the class name must end with "Controller")', get_class($controller[0])));

        }
        if (!preg_match('/^(.+)Action$/', $controller[1], $matchAction)) {
            throw new \InvalidArgumentException(sprintf('The "%s" method does not look like an action method (it does not end with Action)', $controller[1]));
        }

        // Incase there is some form of proxying going on, we will climb up the tree
        $bundleName = get_class($controller[0]);
        try {
            $bundle = $this->getBundleForClass($bundleName);
        }catch(\InvalidArgumentException $e) {
            $parents = class_parents($bundleName);
            foreach($parents as $parent) {
                try {
                    $bundle = $this->getBundleForClass($parent);
                    break;
                }catch(\InvalidArgumentException $ex) {
                }
            }
            
            if(!$bundle) {
                throw $e;
            }
            
        }

        return new TemplateReference($bundle->getName(), $matchController[1], $matchAction[1], $request->getRequestFormat(), $engine);
    }

    /**
     * Returns the Bundle instance in which the given class name is located.
     *
     * @param string $class A fully qualified controller class name
     * @param Bundle $bundle A Bundle instance
     * @throws \InvalidArgumentException
     */
    protected function getBundleForClass($class)
    {
        $namespace = strtr(dirname(strtr($class, '\\', '/')), '/', '\\');
        foreach ($this->kernel->getBundles() as $bundle) {
            if (0 === strpos($namespace, $bundle->getNamespace())) {
                return $bundle;
            }
        }

        throw new \InvalidArgumentException(sprintf('The "%s" class does not belong to a registered bundle.', $class));
    }
}

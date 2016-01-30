<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Templating;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Doctrine\Common\Util\ClassUtils;

/**
 * The TemplateGuesser class handles the guessing of template name based on controller.
 *
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class TemplateGuesser
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var string[]
     */
    private $controllerPatterns;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel             A KernelInterface instance
     * @param string[]        $controllerPatterns Regexps extracting the controller name from its FQN.
     */
    public function __construct(KernelInterface $kernel, array $controllerPatterns = array())
    {
        $controllerPatterns[] = '/Controller\\\(.+)Controller$/';

        $this->kernel = $kernel;
        $this->controllerPatterns = $controllerPatterns;
    }

    /**
     * Guesses and returns the template name to render based on the controller
     * and action names.
     *
     * @param callable $controller An array storing the controller object and action method
     * @param Request  $request    A Request instance
     * @param string   $engine
     *
     * @return TemplateReference template reference
     *
     * @throws \InvalidArgumentException
     */
    public function guessTemplateName($controller, Request $request, $engine = 'twig')
    {
        if (is_object($controller) && method_exists($controller, '__invoke')) {
            $controller = array($controller, '__invoke');
        } elseif (!is_array($controller)) {
            throw new \InvalidArgumentException(sprintf('First argument of %s must be an array callable or an object defining the magic method __invoke. "%s" given.', __METHOD__, gettype($controller)));
        }

        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);

        $matchController = null;
        foreach ($this->controllerPatterns as $pattern) {
            if (preg_match($pattern, $className, $tempMatch)) {
                $matchController = $tempMatch;
                break;
            }
        }
        if (null === $matchController) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (its FQN must match one of the following regexps: "%s")', get_class($controller[0]), implode('", "', $this->controllerPatterns)));
        }

        if ($controller[1] === '__invoke') {
            $matchAction = $matchController;
            $matchController = null;
        } elseif (!preg_match('/^(.+)Action$/', $controller[1], $matchAction)) {
            $matchAction = array(null, $controller[1]);
        }

        $bundle = $this->getBundleForClass($className);

        if ($bundle) {
            while ($bundleName = $bundle->getName()) {
                if (null === $parentBundleName = $bundle->getParent()) {
                    $bundleName = $bundle->getName();

                    break;
                }

                $bundles = $this->kernel->getBundle($parentBundleName, false);
                $bundle = array_pop($bundles);
            }
        } else {
            $bundleName = null;
        }

        return new TemplateReference($bundleName, $matchController[1], $matchAction[1], $request->getRequestFormat(), $engine);
    }

    /**
     * Returns the Bundle instance in which the given class name is located.
     *
     * @param string $class A fully qualified controller class name
     *
     * @return Bundle|null $bundle A Bundle instance
     */
    protected function getBundleForClass($class)
    {
        $reflectionClass = new \ReflectionClass($class);
        $bundles = $this->kernel->getBundles();

        do {
            $namespace = $reflectionClass->getNamespaceName();
            foreach ($bundles as $bundle) {
                if (0 === strpos($namespace, $bundle->getNamespace())) {
                    return $bundle;
                }
            }
            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass);
    }
}

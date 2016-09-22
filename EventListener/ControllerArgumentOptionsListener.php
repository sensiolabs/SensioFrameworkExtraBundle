<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Sensio\Bundle\FrameworkExtraBundle\Annotation\Arg;
use Sensio\Bundle\FrameworkExtraBundle\Exception\LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface as OptionsResolverExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Parses annotation blocks located in controller classes to extract @Arg ones.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ControllerArgumentOptionsListener implements EventSubscriberInterface
{
    /**
     * @var Reader
     */
    private $reader;
    private $argumentOptions;

    public function __construct(Reader $reader, array $argumentOptions)
    {
        $this->reader = $reader;
        $this->argumentOptions = $argumentOptions;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = array($controller, '__invoke');
        }

        if (!is_array($controller)) {
            return;
        }

        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
        $method = new \ReflectionMethod($className, $controller[1]);
        $annotations = $this->reader->getMethodAnnotations($method);
        $context = $className.'::'.$controller[1].'()';

        // FIXME: what about a default configuration on class?
        // should be a different annotation
        // @ArgClass("DateTime", format="Y:m")
        // vs
        // @Arg("start", format="Y:m")

        $parameters = $method->getParameters();
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof Arg) {
                continue;
            }

            $name = $annotation->getArg();
            $options = $annotation->getOptions();
            $parameter = null;

            foreach ($parameters as $param) {
                if ($name === $param->getName()) {
                    $parameter = $param;

                    break;
                }
            }

            if (null === $parameter) {
                throw new LogicException(sprintf('Unknown @Arg argument "%s" on "%s".', $name, $context));
            }

            try {
                $options = $this->resolveOptions($event->getRequest(), $parameter, $options);
            } catch (OptionsResolverExceptionInterface $e) {
                throw new LogicException(sprintf('Wrong @Arg configuration for argument "%s" on "%s".', $name, $context), 0, $e);
            }

            $event->getRequest()->attributes->set($name.'_options', $options);
        }
    }

    private function resolveOptions(Request $request, \ReflectionParameter $parameter, array $options)
    {
        if (null === $class = $this->getParamClass($parameter)) {
            throw new LogicException(sprintf('Missing class type hint on argument "$%s"', $parameter->getName()));
        }

        $argumentMetadata = new ArgumentMetadata($parameter->getName(), $class, false, false, null);
        foreach ($this->argumentOptions as $argumentOption) {
            if ($argumentOption->supports($request, $argumentMetadata)) {
                $resolver = new OptionsResolver();
                $argumentOption->configure($resolver);

                return $resolver->resolve($options);
            }
        }

        return $options;
    }

    private function getParamClass(\ReflectionParameter $parameter)
    {
        if (method_exists('ReflectionParameter', 'getType')) {
            if ($parameter->hasType() && !$parameter->getType()->isBuiltin()) {
                return (string) $parameter->getType();
            }
        } else {
            try {
                $refClass = $parameter->getClass();
            } catch (\ReflectionException $e) {
                // mandatory; extract it from the exception message
                return str_replace(array('Class ', ' does not exist'), '', $e->getMessage());
            }

            return $refClass ? $refClass->getName() : null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }
}

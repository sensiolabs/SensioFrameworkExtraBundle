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
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use Sensio\Bundle\FrameworkExtraBundle\Annotation\Arg;
use Sensio\Bundle\FrameworkExtraBundle\Exception\LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
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
    private $metadataFactory;
    private $argumentOptions;
    private $docBlockFactory;

    public function __construct(Reader $reader, ArgumentMetadataFactory $metadataFactory = null, array $argumentOptions)
    {
        $this->reader = $reader;
        $this->metadataFactory = $metadataFactory;
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
        $argumentMetadata = $this->metadataFactory->createArgumentMetadata($controller);

        $parameters = array();
        foreach ($method->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = true;

            $options = array();
            foreach ($annotations as $annotation) {
                if (!$annotation instanceof Arg) {
                    continue;
                }

                if ($annotation->getArg() !== $parameter->getName()) {
                    continue;
                }

                if (null === $argumentMetadata[$parameter->getName()]->getType()) {
                    throw new LogicException(sprintf('Class type hint on argument "$%s" is required when defining an @Arg annotation.', $parameter->getName()));
                }

                $options = $annotation->getOptions();
            }

            if (null === $argumentMetadata[$parameter->getName()]->getType()) {
                continue;
            }

            try {
                $options = $this->resolveOptions($event->getRequest(), $parameter->getName(), $argumentMetadata[$parameter->getName()], $options);
            } catch (OptionsResolverExceptionInterface $e) {
                throw new LogicException(sprintf('Wrong @Arg configuration for argument "%s" on "%s".', $name, $context), 0, $e);
            }

            $event->getRequest()->attributes->set($parameter->getName().'_options', $options);
        }

        foreach ($annotations as $annotation) {
            if (!$annotation instanceof Arg) {
                continue;
            }

            if (!isset($parameters[$annotation->getArg()])) {
                throw new LogicException(sprintf('Unknown @Arg argument "%s" on "%s".', $annotation->getArg(), $context));
            }
        }
    }

    private function resolveOptions(Request $request, $name, ArgumentMetadata $argumentMetadata, array $options)
    {
        foreach ($this->argumentOptions as $argumentOption) {
            if ($argumentOption->supports($request, $argumentMetadata)) {
                $resolver = new OptionsResolver();
                $argumentOption->configure($resolver);

                return $resolver->resolve($options);
            }
        }

        return $options;
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

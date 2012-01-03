<?php

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The CacheListener class has the responsability to modify the
 * Response object when a controller uses the @Cache annotation.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CacheListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Modifies the Request object to apply cache validation information.
     * 
     * It can also send a 304 response if the autoreturn attribute is enabled.
     * 
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if (!$configuration = $event->getRequest()->attributes->get('_cache')) {
            return;
        }

        if (!$configuration->hasValidationProvider()) {
            return;
        }

        $configuration->loadBuffer($this->container);

        $event->getRequest()->attributes->set('_cache', $configuration);

        // if autoreturn is enabled
        if(!$configuration->getAutoReturn()) {
            return;
        }

        // we generate a response
        $response = $this->populateResponse($configuration);

        // if the response is valid, we return it
        if ($response->isNotModified($event->getRequest())) {
            $returnNotModifiedResponse = function() use ($response) {
                return $response;
            };

            $event->setController($returnNotModifiedResponse);
        }
    }

    /**
     * Modifies the response to apply HTTP expiration/validation header fields.
     *
     * @param FilterResponseEvent $event The notified event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$configuration = $event->getRequest()->attributes->get('_cache')) {
            return;
        }

        $response = $event->getResponse();

        if (!$response->isSuccessful()) {
            return;
        }

        $this->populateResponse($configuration, $response);

        $event->setResponse($response);
    }

    /**
     * Modifies or create a response and apply HTTP expiration/validation
     * header fields for a given annotation configuration.
     *
     * @param Cache $configuration The annotation configuration
     * @param Response $response The response to populate
     */
    protected function populateResponse($configuration, $response = null)
    {
        if (!$response) {
            $response = new Response;
        }

        if (null !== $configuration->getSMaxAge()) {
            $response->setSharedMaxAge($configuration->getSMaxAge());
        }

        if (null !== $configuration->getMaxAge()) {
            $response->setMaxAge($configuration->getMaxAge());
        }

        if (null !== $configuration->getExpires()) {
            $date = \DateTime::createFromFormat('U', strtotime($configuration->getExpires()), new \DateTimeZone('UTC'));
            $response->setExpires($date);
        }

        if ($configuration->isPublic()) {
            $response->setPublic();
        }

        if ($configuration->buffer->has('etag')) {
            $response->setETag($configuration->buffer->get('etag'));
        }

        if ($configuration->buffer->has('last_modified')) {
            $response->setLastModified($configuration->buffer->get('last_modified'));
        }

        return $response;
    }
}

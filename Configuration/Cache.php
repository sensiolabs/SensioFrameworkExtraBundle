<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Caching\CacheValidationProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Cache class handles the @Cache annotation parts.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @Annotation
 */
class Cache extends ConfigurationAnnotation
{
    /**
     * The buffer.
     * 
     * The buffer is a place to store data from the validation provider. As annotation
     * configuration's is stored in the request, it's a bridge between the validation
     * provider and the controller, it can be used to avoid duplicate code between them.
     *
     * @var ParameterBag
     */
    public $buffer;

    /**
     * The expiration date as a valid date for the strtotime() function.
     *
     * @var string
     */
    protected $expires;

    /**
     * The number of seconds that the response is considered fresh by a private
     * cache like a web browser.
     *
     * @var integer
     */
    protected $maxage;

    /**
     * The number of seconds that the response is considered fresh by a public
     * cache like a reverse proxy cache.
     *
     * @var integer
     */
    protected $smaxage;

    /**
     * Whether or not the response is public.
     *
     * @var Boolean
     */
    protected $public;

    /**
     * The validation provider.
     *
     * @var CacheValidationProviderInterface
     */
    protected $validation;

    /**
     * Whether or not the response should be sent on kernel.controller event 
     * if it's a valid one.
     *
     * @var Boolean
     */
    protected $autoreturn;

    /**
     * Constructor.
     * 
     * Creates the buffer, tries to load the validation provider, and finally
     * initialize other properties with the parent class constructor.
     *
     * @param array $values Attributes from the annotation
     */
    public function __construct(array $values)
    {
        $this->buffer = new ParameterBag;

        if (isset($values['validation'])) {
            $validation = new $values['validation'];

            if (!$validation instanceof CacheValidationProviderInterface) {
                throw new \InvalidArgumentException('The "validation" attribute for a Cache Annotation
                 must be a class implementing the CacheValidationProviderInterface.');
            }

            $this->validation = $validation;
        }

        // we unset those values to avoid the parent constructor setting object's properties
        unset($values['validation'], $values['buffer']);

        parent::__construct($values);
    }

    /**
     * Returns whether or not the autoreturn is enabled.
     *
     * @return Boolean
     */
    public function getAutoReturn()
    {
        return (Boolean) $this->autoreturn;
    }

    /**
     * Sets the autoreturn value
     *
     * @param Boolean $public A boolean value
     */
    public function setAutoReturn($autoreturn)
    {
        $this->autoreturn = (Boolean) $autoreturn;
    }

    /**
     * Returns wether or not the configuration has a validation provider.
     *
     * @return Boolean
     */
    public function hasValidationProvider()
    {
        if (null === $this->validation) {
            return false;
        }

        return true;
    }

    /**
     * Returns the expiration date for the Expires header field.
     *
     * @return string
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Sets the expiration date for the Expires header field.
     *
     * @param string $expires A valid php date
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * Sets the number of seconds for the max-age cache-control header field.
     *
     * @param integer $maxage A number of seconds
     */
    public function setMaxAge($maxage)
    {
        $this->maxage = $maxage;
    }

    /**
     * Returns the number of seconds the response is considered fresh by a
     * private cache.
     *
     * @return integer
     */
    public function getMaxAge()
    {
        return $this->maxage;
    }

    /**
     * Sets the number of seconds for the s-maxage cache-control header field.
     *
     * @param integer $smaxage A number of seconds
     */
    public function setSMaxAge($smaxage)
    {
        $this->smaxage = $smaxage;
    }

    /**
     * Returns the number of seconds the response is considered fresh by a
     * public cache.
     *
     * @return integer
     */
    public function getSMaxAge()
    {
        return $this->smaxage;
    }

    /**
     * Returns whether or not a response is public.
     *
     * @return Boolean
     */
    public function isPublic()
    {
        return (Boolean) $this->public;
    }

    /**
     * Sets a response public.
     *
     * @param Boolean $public A boolean value
     */
    public function setPublic($public)
    {
        $this->public = (Boolean) $public;
    }

    /**
     * Loads the buffer with validation provider's etag and last_modified headers.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function loadBuffer(ContainerInterface $container)
    {
        if ($this->validation instanceof ContainerAwareInterface) {
            $this->validation->setContainer($container);
        }

        $bufferParameters = $this->validation->process();

        // if it's an array, we store the process() method's return in the buffer
        if (null !== $bufferParameters && is_array($bufferParameters)) {
            $this->buffer->set('parameters', $bufferParameters);
        }

        if (null !== $eTag = $this->validation->getETag()) {
            $this->buffer->set('etag', $eTag);
        }

        if (null !== $lastModified = $this->validation->getLastModified()) {
            $this->buffer->set('last_modified', $lastModified);
        }
    }

    /**
     * Returns the annotation alias name.
     *
     * @return string
     * @see ConfigurationInterface
     */
    public function getAliasName()
    {
        return 'cache';
    }
}

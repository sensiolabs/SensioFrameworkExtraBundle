<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

/**
 * The Security class handles the Security annotation.
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 * @Annotation
 */
class IsGranted extends ConfigurationAnnotation
{
    /**
     * Sets the first argument that will be passed to isGranted().
     *
     * @var mixed
     */
    private $attribute;

    /**
     * Sets the second argument passed to isGranted().
     *
     * @var mixed
     */
    private $subject;

    /**
     * The message of the exception - has a nice default if not set.
     *
     * @var string
     */
    private $message;

    /**
     * If set, will throw Symfony\Component\HttpKernel\Exception\HttpException
     * with the given $statusCode.
     * If null, Symfony\Component\Security\Core\Exception\AccessDeniedException.
     * will be used.
     *
     * @var int|null
     */
    private $statusCode;

    public function setAttribute($attribute)
    {
        if (\is_array($attribute)) {
            if (\count($attribute) > 1) {
                @trigger_error(sprintf('Passing multiple Security attributes to the "%s" annotation is deprecated since SensioFrameworkExtraBundle 5.6.', __CLASS__));
            } else {
                $attribute = $attribute[0];
            }
        }

        $this->attribute = $attribute;
    }

    public function setAttributes($attributes)
    {
        @trigger_error(sprintf('The attributes option of the "%s" annotation is deprecated since SensioFrameworkExtraBundle 5.6. Use the "attribute" option instead.', __CLASS__));

        $this->setAttribute($attributes);
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function setValue($value)
    {
        $this->setAttribute($value);
    }

    public function getAliasName()
    {
        return 'is_granted';
    }

    public function allowArray()
    {
        return true;
    }
}

<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Configuration;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The Template class handles the @Template annotation parts.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @Annotation
 */
class Template extends ConfigurationAnnotation
{
    /**
     * The template reference.
     *
     * @var TemplateReference
     */
    protected $template;

    /**
     * The template engine used when a specific template isnt specified
     *
     * @var string
     */
    protected $engine = 'twig';

    /**
     * The associative array of template variables.
     *
     * @var array
     */
    protected $vars = array();

    /**
     * Should the template be streamed?
     *
     * @var Boolean
     */
    protected $streamable = false;

    /**
     * Returns the array of templates variables.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param Boolean $streamable
     */
    public function setIsStreamable($streamable)
    {
        $this->streamable = $streamable;
    }

    /**
     * @return Boolean
     */
    public function isStreamable()
    {
        return (Boolean) $this->streamable;
    }

    /**
     * Sets the template variables
     *
     * @param array $vars The template variables
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    }

    /**
     * Returns the engine used when guessing template names
     *
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Sets the engine used when guessing template names
     *
     * @param string
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * Sets the template logic name.
     *
     * @param string $template The template logic name
     */
    public function setValue($template)
    {
        $this->setTemplate($template);
    }

    /**
     * Returns the template reference.
     *
     * @return TemplateReference
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the template reference.
     *
     * @param TemplateReference|string $template The template reference
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Returns the annotation alias name.
     *
     * @return string
     * @see ConfigurationInterface
     */
    public function getAliasName()
    {
        return 'template';
    }

    /**
     * Only one template directive is allowed
     *
     * @return Boolean
     * @see ConfigurationInterface
     */
    public function allowArray()
    {
        return false;
    }
}

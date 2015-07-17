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

use Symfony\Component\Templating\TemplateReference as BaseTemplateReference;

/**
 * Internal representation of a template called with namespace.
 *
 * @author Evgeniy Sokolov <ewgraf@gmail.com>
 */
class NamespaceTemplateReference extends BaseTemplateReference
{
    public function __construct($namespace = null, $controller = null, $name = null, $format = null, $engine = null)
    {
        $this->parameters = array(
            'namespace'  => $namespace,
            'controller' => $controller,
            'name'       => $name,
            'format'     => $format,
            'engine'     => $engine,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        $controller = str_replace('\\', '/', $this->get('controller'));

        return (empty($controller) ? '' : $controller.'/').$this->get('name').'.'.$this->get('format').'.'.$this->get('engine');
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName()
    {
        return sprintf('@%s/%s', $this->parameters['namespace'], $this->getPath());
    }
}

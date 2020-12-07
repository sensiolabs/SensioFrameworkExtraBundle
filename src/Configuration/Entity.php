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
 * Doctrine-specific ParamConverter with an easier syntax.
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 * @Annotation
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Entity extends ParamConverter
{
    public function setExpr($expr)
    {
        $options = $this->getOptions();
        $options['expr'] = $expr;

        $this->setOptions($options);
    }

    /**
     * @param array|string $data
     */
    public function __construct(
        $data = [],
        string $expr = null,
        string $class = null,
        array $options = [],
        bool $isOptional = false,
        string $converter = null
    ) {
        $values = [];
        if (\is_string($data)) {
            $values['value'] = $data;
        } else {
            $values = $data;
        }

        $values['expr'] = $values['expr'] ?? $expr;

        parent::__construct($values, $class, $options, $isOptional, $converter);

        $this->setExpr($values['expr']);
    }
}

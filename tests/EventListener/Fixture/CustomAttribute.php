<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class CustomAttribute implements ConfigurationInterface
{
    private $custom;

    public function __construct(
        array $values = [],
        string $custom = null
    ) {
        $this->custom = $values['custom'] ?? $custom;
    }

    public function getCustom()
    {
        return $this->custom;
    }

    public function getAliasName()
    {
        return 'custom';
    }

    public function allowArray()
    {
        return false;
    }
}

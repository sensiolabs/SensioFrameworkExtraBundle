<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Hashids\Hashids;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * HashidsParamConverter.
 *
 * @author Maarten de Boer <maarten@cloudstek.nl>
 */
class HashidsParamConverter implements ParamConverterInterface
{
    /**
     * @var ParamConverterInterface|null
     */
    protected $inner;

    /**
     * @var Hashids|null
     */
    protected $hashids;

    public function __construct(ParamConverterInterface $inner = null, Hashids $hashids = null)
    {
        $this->inner = $inner;
        $this->hashids = $hashids;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();
        $name = !empty($options['id']) ? $options['id'] : $configuration->getName();

        if (is_array($name)) {
            $ids = [];
            foreach ($name as $field) {
                $request->attributes->set($field, $this->decodeId($request->attributes->get($field)));
            }
        } elseif ($request->attributes->has($name)) {
            $request->attributes->set($name, $this->decodeId($request->attributes->get($name)));
        } elseif ($request->attributes->has('id') && empty($options['id'])) {
            $request->attributes->set('id', $this->decodeId($request->attributes->get('id')));
        }

        return $this->inner->apply($request, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $this->inner || null === $this->hashids) {
            return false;
        }

        return $this->inner->supports($configuration);
    }

    /**
     * Decode identifier.
     *
     * @param string|int|null $id
     *
     * @return int|null
     */
    protected function decodeId($id)
    {
        if (!is_string($id) && !is_int($id)) {
            return null;
        }

        if (!empty($id) && is_string($id) && !is_numeric($id)) {
            $hashid = array_filter($this->hashids->decode($id));

            if (count($hashid) > 0) {
                $id = (int) $hashid[0];
            }
        }

        return (int) $id;
    }
}

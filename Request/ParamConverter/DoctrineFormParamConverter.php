<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class DoctrineFormParamConverter extends DoctrineParamConverter
{
    private $formFactory;

    public function __construct(ManagerRegistry $registry = null, FormFactoryInterface $formFactory = null)
    {
        parent::__construct($registry);
        $this->formFactory = $formFactory;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $options = $this->getOptions($configuration);
        $key     = isset($options['id']) ? $options['id'] : 'id';

        if ($request->attributes->has($key)) {
            parent::apply($request, $configuration);
        }

        $data = $request->attributes->get($configuration->getName());

        $form = $this->formFactory->create(
            $options['form_type'],
            $data,
            isset($options['form_options']) ? $options['form_options'] : array()
        );

        $form->bind($request);

        if ( ! $form->isValid()) {
            throw new HttpException(400, $form->getErrorsAsString());
        }

        $request->attributes->set($configuration->getName(), $form->getData());

        return true;
    }

    public function supports(ConfigurationInterface $configuration)
    {
        if (!$this->formFactory) {
            return false;
        }

        $options = $this->getOptions($configuration);

        if (!isset($options['form_type'])) {
            return false;
        }

        return parent::supports($configuration);
    }
}

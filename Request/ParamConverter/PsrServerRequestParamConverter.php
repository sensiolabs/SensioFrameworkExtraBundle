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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Converts HttpFoundation Request to PSR-7 ServerRequest using the bridge.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class PsrServerRequestParamConverter implements ParamConverterInterface
{
    /**
     * @var array
     */
    private static $supportedTypes = array(
        'Psr\Http\Message\ServerRequestInterface' => true,
        'Psr\Http\Message\RequestInterface' => true,
        'Psr\Http\Message\MessageInterface' => true,
    );
    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    public function __construct(HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->httpMessageFactory = $httpMessageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $request->attributes->set($configuration->getName(), $this->httpMessageFactory->createRequest($request));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return isset(self::$supportedTypes[$configuration->getClass()]);
    }
}

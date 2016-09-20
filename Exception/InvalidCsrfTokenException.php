<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Exception;

class InvalidCsrfTokenException extends \RuntimeException
{
    private $validToken;

    public function __construct($message = 'Invalid CSRF token.')
    {
        parent::__construct($message, 403);
    }

    /**
     * @return string
     */
    public function getValidToken()
    {
        return $this->validToken;
    }

    /**
     * @param string $validToken
     */
    public function setValidToken($validToken)
    {
        $this->validToken = $validToken;
    }
}

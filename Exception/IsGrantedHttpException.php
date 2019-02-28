<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class IsGrantedHttpException extends HttpException implements IsGrantedExceptionInterface
{
    private $attributes = [];
    private $subject;

    public function __construct($attributes, $subject, $statusCode, $message)
    {
        $this->attributes = $attributes;
        $this->subject = $subject;

        parent::__construct($statusCode, $message);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getSubject()
    {
        return $this->subject;
    }
}

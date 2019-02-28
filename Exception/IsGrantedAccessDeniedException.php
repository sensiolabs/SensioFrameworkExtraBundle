<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IsGrantedAccessDeniedException extends AccessDeniedException implements IsGrantedExceptionInterface
{
    private $attributes = [];
    private $subject;

    public function __construct($attributes, $subject, $message)
    {
        $this->attributes = $attributes;
        $this->subject = $subject;

        parent::__construct($message);
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

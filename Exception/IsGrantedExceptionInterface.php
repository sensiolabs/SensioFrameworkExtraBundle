<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Exception;

interface IsGrantedExceptionInterface
{
    public function getAttributes();

    public function getSubject();
}

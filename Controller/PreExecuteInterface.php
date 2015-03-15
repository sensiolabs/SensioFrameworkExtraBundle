<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

interface PreExecuteInterface
{
    public function preExecute(Request $request, $methodName);
}
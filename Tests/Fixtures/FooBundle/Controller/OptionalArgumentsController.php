<?php

namespace Tests\Fixtures\FooBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/optional-arguments")
 */
class OptionalArgumentsController
{
    /**
     * @Route("/with-default-followed-by-mandatory", defaults={"e" = null})
     * @ParamConverter(name="d", class="Tests\Fixtures\FooBundle\Entity\Foo")
     */
    public function withDefaultFollowedByMandatory($d = null, $e)
    {
        return new Response(null === $d ? 'yes' : 'no');
    }
}

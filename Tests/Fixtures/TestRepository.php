<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Fixtures;

use Doctrine\Common\Persistence\ObjectRepository;

interface TestRepository extends ObjectRepository
{

    function customMethod($group, $user);

    function customArrayMethod(array $users);

    function customClassMethod(\stdClass $user);

    function customDefaultMethod($group, $user = 'test');

}

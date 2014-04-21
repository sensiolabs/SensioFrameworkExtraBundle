<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Configuration;

class ConfigurationAnnotationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testUndefinedSetterThrowsException()
    {
        $this->getMockForAbstractClass('Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation', array(
            array(
                'doesNotExists' => true,
            ),
        ));
    }
}

<?php

namespace Sensio\Bundle\FrameworkExtraBunde\Tests\Configuration;

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

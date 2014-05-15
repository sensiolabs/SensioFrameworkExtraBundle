<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Utils;

use Sensio\Bundle\FrameworkExtraBundle\Utils\TimeUtils;

class TimeUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testDurationToSeconds()
    {
        $intervals = array(
            '0'               => 0,
            '86400'           => 86400,
            '0 second'        => 0,
            '1 second'        => 1,
            '1 minute'        => 60,
            '1 hour'          => 60 * 60,
            '1 day'           => 60 * 60 * 24,
            '1 week'          => 60 * 60 * 24 * 7,
            '1 month'         => 31 * 60 * 60 * 24,
            '1 year'          => 60 * 60 * 24 * 365,
            '1 day - 12hours' => 60 * 60 * 24 - 12 * 60 * 60,
            '-1 hour'         => - 60 * 60
        );

        foreach ($intervals as $interval => $expected) {
            $this->assertSame(
                $expected,
                TimeUtils::durationToSeconds($interval),
                sprintf('%s equals %s seconds', $interval, $expected)
            );
        }
    }
}

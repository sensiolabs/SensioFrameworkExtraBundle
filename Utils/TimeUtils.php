<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Utils;

class TimeUtils
{
    /**
     * This function will return the number of seconds that represents a duration given as a readable format.
     * Only second/day/week/month/year symbols are supported.
     *
     * <pre>
     * TimeUtils::durationToSeconds('3600')    === 3600
     * TimeUtils::durationToSeconds('2 days')  === 60 * 60 * 24 * 2
     * TimeUtils::durationToSeconds('1 week')  === 60 * 60 * 24 * 7
     * TimeUtils::durationToSeconds('1 month') === 60 * 60 * 24 * 31
     * TimeUtils::durationToSeconds('1 year')  === 60 * 60 * 24 * 365
     * TimeUtils::durationToSeconds('-2 days') === - 60 * 60 * 24 * 2
     * </pre>
     *
     * @param int|string $duration A duration as a relative format supported by the parser used for strtotime()
     *
     * @see http://www.php.net/manual/en/datetime.formats.relative.php
     *
     * @return int
     */
    public static function durationToSeconds($duration)
    {
        if (is_numeric($duration)) {
            return (int) $duration;
        }

        $date = new \DateTime();

        return $date
            // can't use new \DateTime("@0") as it will add 3600 seconds to the final output on php 5.3.9 - 5.4.7
            ->setTimestamp(0)
            ->add(\DateInterval::createFromDateString($duration))
            ->getTimestamp()
        ;
    }
}

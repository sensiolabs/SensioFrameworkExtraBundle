<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class Psr7RequestTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testController($url)
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertEquals('ok', $crawler->filterXPath('//body')->html());
    }

    public static function urlProvider()
    {
        return array(
            array('/action-arguments/normal/'),
            array('/action-arguments/invoke/'),
        );
    }
}

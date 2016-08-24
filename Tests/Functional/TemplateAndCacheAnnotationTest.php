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

class TemplateAnnotationTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testTemplateAndCacheController($url)
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $url, array(), array(), array('HTTP_If-None-Match' => sprintf('"%s"', hash('sha256', 'ThisIsMyETAG'))));

        $this->assertEquals(304, $client->getResponse()->getStatusCode());
    }

    public static function urlProvider()
    {
        return array(
            array('/templateandcache'),
            array('/cacheonly'),
        );
    }
}

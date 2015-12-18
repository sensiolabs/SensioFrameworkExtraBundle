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
    public function testController($url, $checkHtml)
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertEquals($checkHtml, $crawler->filterXPath('//body')->html());
    }

    public static function urlProvider()
    {
        return array(
            array('/multi/one-template/1/', 'bar'),
            array('/multi/one-template/2/', 'bar'),
            array('/multi/one-template/3/', 'bar'),
            array('/multi/one-template/4/', 'foo bar baz'),
            array('/invokable/predefined/service/', 'bar'),
            array('/invokable/class-level/service/', 'bar'),
            array('/simple/multiple/', 'a, b, c'),
            array('/simple/multiple/henk/bar/', 'henk, bar, c'),
            array('/simple/multiple-with-vars/', 'a, b'),
            array('/invokable/predefined/container/', 'bar'),
            array('/invokable/variable/container/the-var/', 'the-var'),
            array('/invokable/another-variable/container/another-var/', 'another-var'),
            array('/invokable/variable/container/the-var/another-var/', 'the-var,another-var'),
            array('/no-listener/', 'I did not get rendered via twig'),
            array('/streamed/', 'foo, bar'),
        );
    }
}

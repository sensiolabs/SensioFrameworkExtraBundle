<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @requires function \Symfony\Component\HttpFoundation\Request::preferSafeContent
 */
class ContentSafeTest extends WebTestCase
{
    public function testResponseSafePreferenceApplied()
    {
        $client = self::createClient();
        $client->request('GET', '/content-safe', [], [], [
            'HTTP_Prefer' => 'safe',
            'HTTPS' => true,
        ]);

        $this->assertTrue($client->getResponse()->headers->has('preference-applied'));
        $this->assertEquals('safe', $client->getResponse()->headers->get('preference-applied'));

        $this->assertTrue($client->getResponse()->headers->has('vary'));
        $this->assertEquals('Prefer', $client->getResponse()->headers->get('vary'));
    }

    public function testResponseSafePreferenceNotAppliedOnNonAnnotatedActions()
    {
        $client = self::createClient();
        $client->request('GET', '/content-safe/non-safe', [], [], [
            'HTTP_Prefer' => 'safe',
            'HTTPS' => true,
        ]);

        $this->assertFalse($client->getResponse()->headers->has('preference-applied'));
        $this->assertFalse($client->getResponse()->headers->has('vary'));
    }

    public function testResponseSafePreferenceNotAppliedForNonHttps()
    {
        $client = self::createClient();
        $client->request('GET', '/content-safe', [], [], [
            'HTTP_Prefer' => 'safe',
        ]);

        $this->assertFalse($client->getResponse()->headers->has('preference-applied'));
        $this->assertFalse($client->getResponse()->headers->has('vary'));
    }
}

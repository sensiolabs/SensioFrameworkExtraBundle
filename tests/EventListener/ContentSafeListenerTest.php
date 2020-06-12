<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ContentSafe;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ContentSafeListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @requires function \Symfony\Component\HttpFoundation\Request::preferSafeContent
 */
class ContentSafeListenerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->listener = new ContentSafeListener();
        $this->contentSafe = new ContentSafe([]);
        $this->request = $this->createRequest($this->contentSafe);
        $this->response = new Response();
        $this->event = $this->createEventMock($this->request, $this->response);
    }

    public function testContentSafeResponse()
    {
        $this->assertEmpty($this->response->getVary());
        $this->listener->onKernelResponse($this->event);

        $this->assertTrue($this->event->getResponse()->headers->has('Preference-Applied'));
        $this->assertEquals(['Prefer'], $this->response->getVary());
    }

    private function createRequest(ContentSafe $contentSafe = null): Request
    {
        return new Request([], [], [
            '_content_safe' => $contentSafe,
        ], [], [], [
            'HTTP_Prefer' => 'safe',
            'HTTPS' => 'true',
        ]);
    }

    private function createEventMock(Request $request, Response $response): ResponseEvent
    {
        return new ResponseEvent($this->getKernel(), $request, HttpKernelInterface::MASTER_REQUEST, $response);
    }

    private function getKernel()
    {
        return $this->getMockBuilder(HttpKernelInterface::class)->getMock();
    }
}

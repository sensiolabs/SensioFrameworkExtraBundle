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

use Doctrine\Common\Annotations\AnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAttributeAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAttributeAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCacheAttributeAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCustomAttributeAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCustomAttributeAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerCustomAttributeAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerEntityAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerEntityAttributeAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerIsGrantedAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerIsGrantedAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerIsGrantedAttributeAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerIsGrantedAttributeAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerMultipleCacheAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerMultipleCacheAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerParamConverterAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerParamConverterAttributeAtClassAndMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerSecurityAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerSecurityAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerSecurityAttributeAtClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerSecurityAttributeAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerTemplateAtMethod;
use Sensio\Bundle\FrameworkExtraBundle\Tests\EventListener\Fixture\FooControllerTemplateAttributeAtMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ControllerListenerTest extends \PHPUnit\Framework\TestCase
{
    private $event;
    private $listener;
    private $request;

    protected function setUp(): void
    {
        $this->listener = new ControllerListener(new AnnotationReader());
        $this->request = $this->createRequest();

        // trigger the autoloading of the @Cache annotation
        class_exists(Cache::class);
    }

    protected function tearDown(): void
    {
        $this->listener = null;
        $this->request = null;
    }

    public function testCacheAnnotationAtMethod()
    {
        $controller = new FooControllerCacheAtMethod();

        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    /**
     * @requires PHP 8.0
     */
    public function testCacheAttributeAtMethod()
    {
        $controller = new FooControllerCacheAttributeAtMethod();

        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    public function testCacheAnnotationAtClass()
    {
        $controller = new FooControllerCacheAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClass::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    /**
     * @requires PHP 8.0
     */
    public function testCacheAttributeAtClass()
    {
        $controller = new FooControllerCacheAttributeAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClass::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    public function testCacheAnnotationAtClassAndMethod()
    {
        $controller = new FooControllerCacheAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClassAndMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());

        $this->event = $this->getFilterControllerEvent([$controller, 'bar2Action'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAtClassAndMethod::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    /**
     * @requires PHP 8.0
     */
    public function testCacheAttributeAtClassAndMethod()
    {
        $controller = new FooControllerCacheAttributeAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAttributeAtClassAndMethod::METHOD_SMAXAGE, $this->getReadedCache()->getSMaxAge());

        $this->event = $this->getFilterControllerEvent([$controller, 'bar2Action'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getReadedCache());
        $this->assertEquals(FooControllerCacheAttributeAtClassAndMethod::CLASS_SMAXAGE, $this->getReadedCache()->getSMaxAge());
    }

    /**
     * @requires PHP 8.0
     */
    public function testCustomAttributeAtMethod()
    {
        $controller = new FooControllerCustomAttributeAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getCustomAttribute());
        $this->assertEquals(FooControllerCustomAttributeAtClassAndMethod::METHOD_CUSTOM, $this->getCustomAttribute()->getCustom());
    }

    /**
     * @requires PHP 8.0
     */
    public function testCustomAttributeAtClass()
    {
        $controller = new FooControllerCustomAttributeAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getCustomAttribute());
        $this->assertEquals(FooControllerCustomAttributeAtClass::CLASS_CUSTOM, $this->getCustomAttribute()->getCustom());
    }

    /**
     * @requires PHP 8.0
     */
    public function testCustomAttributeAtClassAndMethod()
    {
        $controller = new FooControllerCustomAttributeAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getCustomAttribute());
        $this->assertEquals(FooControllerCustomAttributeAtClassAndMethod::METHOD_CUSTOM, $this->getCustomAttribute()->getCustom());

        $this->event = $this->getFilterControllerEvent([$controller, 'bar2Action'], $this->request);
        $this->listener->onKernelController($this->event);

        $this->assertNotNull($this->getCustomAttribute());
        $this->assertEquals(FooControllerCustomAttributeAtClassAndMethod::CLASS_CUSTOM, $this->getCustomAttribute()->getCustom());
    }

    public function testMultipleAnnotationsOnClassThrowsExceptionUnlessConfigurationAllowsArray()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Multiple "cache" annotations are not allowed');

        $controller = new FooControllerMultipleCacheAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);
    }

    public function testMultipleAnnotationsOnMethodThrowsExceptionUnlessConfigurationAllowsArray()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Multiple "cache" annotations are not allowed');

        $controller = new FooControllerMultipleCacheAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);
    }

    public function testMultipleParamConverterAnnotationsOnMethod()
    {
        $paramConverter = new ParamConverter([]);
        $controller = new FooControllerParamConverterAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_converters');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(ParamConverter::class, $annotations[0]);
        $this->assertEquals('test', $annotations[0]->getName());

        $this->assertArrayHasKey(1, $annotations);
        $this->assertInstanceOf(ParamConverter::class, $annotations[1]);
        $this->assertEquals('test2', $annotations[1]->getName());

        $this->assertCount(2, $annotations);
    }

    /**
     * @requires PHP 8.0
     */
    public function testMultipleParamConverterAttributesOnMethod()
    {
        $paramConverter = new ParamConverter([]);
        $controller = new FooControllerParamConverterAttributeAtClassAndMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_converters');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(ParamConverter::class, $annotations[0]);
        $this->assertEquals('test', $annotations[0]->getName());

        $this->assertArrayHasKey(1, $annotations);
        $this->assertInstanceOf(ParamConverter::class, $annotations[1]);
        $this->assertEquals('test2', $annotations[1]->getName());

        $this->assertCount(2, $annotations);
    }

    public function testEntityAnnotationOnMethod()
    {
        $controller = new FooControllerEntityAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_converters');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(Entity::class, $annotations[0]);
        $this->assertEquals('foo', $annotations[0]->getName());
    }

    /**
     * @requires PHP 8.0
     */
    public function testEntityAttributeOnMethod()
    {
        $controller = new FooControllerEntityAttributeAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_converters');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(Entity::class, $annotations[0]);
        $this->assertEquals('foo', $annotations[0]->getName());
    }

    public function testIsGrantedAnnotationOnClass()
    {
        $controller = new FooControllerIsGrantedAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_is_granted');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(IsGranted::class, $annotations[0]);
        $this->assertEquals('ROLE_USER', $annotations[0]->getAttributes());
    }

    /**
     * @requires PHP 8.0
     */
    public function testIsGrantedAttributeOnClass()
    {
        $controller = new FooControllerIsGrantedAttributeAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_is_granted');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(IsGranted::class, $annotations[0]);
        $this->assertEquals('ROLE_USER', $annotations[0]->getAttributes());
    }

    public function testIsGrantedAnnotationOnMethod()
    {
        $controller = new FooControllerIsGrantedAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_is_granted');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(1, $annotations);
        $this->assertInstanceOf(IsGranted::class, $annotations[0]);
        $this->assertEquals('ROLE_USER', $annotations[0]->getAttributes());
        $this->assertInstanceOf(IsGranted::class, $annotations[1]);
        $this->assertEquals('FOO_SHOW', $annotations[1]->getAttributes());
        $this->assertEquals('foo', $annotations[1]->getSubject());
    }

    /**
     * @requires PHP 8.0
     */
    public function testIsGrantedAttributeOnMethod()
    {
        $controller = new FooControllerIsGrantedAttributeAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_is_granted');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(1, $annotations);
        $this->assertInstanceOf(IsGranted::class, $annotations[0]);
        $this->assertEquals('ROLE_USER', $annotations[0]->getAttributes());
        $this->assertInstanceOf(IsGranted::class, $annotations[1]);
        $this->assertEquals('FOO_SHOW', $annotations[1]->getAttributes());
        $this->assertEquals('foo', $annotations[1]->getSubject());
    }

    public function testSecurityAnnotationOnClass()
    {
        $controller = new FooControllerSecurityAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_security');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(Security::class, $annotations[0]);
        $this->assertEquals("is_granted('ROLE_USER')", $annotations[0]->getExpression());
    }

    /**
     * @requires PHP 8.0
     */
    public function testSecurityAttributeOnClass()
    {
        $controller = new FooControllerSecurityAttributeAtClass();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_security');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(Security::class, $annotations[0]);
        $this->assertEquals("is_granted('ROLE_USER')", $annotations[0]->getExpression());
    }

    public function testSecurityAnnotationOnMethod()
    {
        $controller = new FooControllerSecurityAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_security');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(Security::class, $annotations[0]);
        $this->assertEquals("is_granted('ROLE_USER') and is_granted('FOO_SHOW', foo)", $annotations[0]->getExpression());
    }

    /**
     * @requires PHP 8.0
     */
    public function testSecurityAttributeOnMethod()
    {
        $controller = new FooControllerSecurityAttributeAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotations = $this->request->attributes->get('_security');
        $this->assertNotNull($annotations);
        $this->assertArrayHasKey(0, $annotations);
        $this->assertInstanceOf(Security::class, $annotations[0]);
        $this->assertEquals("is_granted('ROLE_USER') and is_granted('FOO_SHOW', foo)", $annotations[0]->getExpression());
    }

    public function testTemplateAnnotationOnMethod()
    {
        $controller = new FooControllerTemplateAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);
        $annotation = $this->request->attributes->get('_template');
        $this->assertNotNull($annotation);
        $this->assertInstanceOf(Template::class, $annotation);
        $this->assertEquals('templates/bar.html.twig', $annotation->getTemplate());
        $this->assertEquals(['foo'], $annotation->getVars());
    }

    /**
     * @requires PHP 8.0
     */
    public function testTemplateAttributeOnMethod()
    {
        $controller = new FooControllerTemplateAttributeAtMethod();
        $this->event = $this->getFilterControllerEvent([$controller, 'barAction'], $this->request);
        $this->listener->onKernelController($this->event);

        $annotation = $this->request->attributes->get('_template');
        $this->assertNotNull($annotation);
        $this->assertInstanceOf(Template::class, $annotation);
        $this->assertEquals('templates/bar.html.twig', $annotation->getTemplate());
        $this->assertEquals(['foo'], $annotation->getVars());
    }

    private function createRequest(Cache $cache = null)
    {
        return new Request([], [], [
            '_cache' => $cache,
        ]);
    }

    private function getFilterControllerEvent($controller, Request $request)
    {
        $mockKernel = $this->getMockForAbstractClass(\Symfony\Component\HttpKernel\Kernel::class, ['test', '']);

        return new ControllerEvent($mockKernel, $controller, $request, HttpKernelInterface::MASTER_REQUEST);
    }

    private function getReadedCache()
    {
        return $this->request->attributes->get('_cache');
    }

    private function getCustomAttribute()
    {
        return $this->request->attributes->get('_custom');
    }
}

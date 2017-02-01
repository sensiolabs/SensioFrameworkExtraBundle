<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Templating;

use Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class TemplateGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    private $bundles = array();

    public function setUp()
    {
        $this->bundles['FooBundle'] = $this->getBundle('FooBundle', 'Sensio\Bundle\FrameworkExtraBundle\Tests\Templating\Fixture\FooBundle');
        $this->bundles['BarBundle'] = $this->getBundle('BarBundle', 'Sensio\Bundle\FrameworkExtraBundle\Tests\Templating\Fixture\BarBundle', 'FooBundle');
        $this->bundles['FooBarBundle'] = $this->getBundle('FooBarBundle', 'Sensio\Bundle\FrameworkExtraBundle\Tests\Templating\Fixture\FooBarBundle', 'BarBundle');

        $this->kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
        $this->kernel
            ->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array_values($this->bundles)));
    }

    public function testGuessTemplateName()
    {
        $this->kernel
            ->expects($this->never())
            ->method('getBundle');

        $templateGuesser = new TemplateGuesser($this->kernel);
        $templateReference = $templateGuesser->guessTemplateName(array(
            new Fixture\FooBundle\Controller\FooController(),
            'indexAction',
        ), new Request());

        $this->assertEquals('FooBundle:Foo:index.html.twig', (string) $templateReference);
    }

    public function testGuessTemplateNameWithParentBundle()
    {
        $this->kernel
            ->expects($this->once())
            ->method('getBundle')
            ->with($this->equalTo('FooBundle'), false)
            ->will($this->returnValue(array($this->bundles['BarBundle'], $this->bundles['FooBundle'])));

        $templateGuesser = new TemplateGuesser($this->kernel);
        $templateReference = $templateGuesser->guessTemplateName(array(
            new Fixture\BarBundle\Controller\BarController(),
            'indexAction',
        ), new Request());

        $this->assertEquals('FooBundle:Bar:index.html.twig', (string) $templateReference);
    }

    public function testGuessTemplateNameWithCascadingParentBundle()
    {
        $this->kernel
            ->expects($this->at(1))
            ->method('getBundle')
            ->with($this->equalTo('BarBundle'), false)
            ->will($this->returnValue(array($this->bundles['FooBarBundle'], $this->bundles['BarBundle'])));

        $this->kernel
            ->expects($this->at(2))
            ->method('getBundle')
            ->with($this->equalTo('FooBundle'), false)
            ->will($this->returnValue(array($this->bundles['FooBarBundle'], $this->bundles['BarBundle'], $this->bundles['FooBundle'])));

        $templateGuesser = new TemplateGuesser($this->kernel);
        $templateReference = $templateGuesser->guessTemplateName(array(
            new Fixture\FooBarBundle\Controller\FooBarController(),
            'indexAction',
        ), new Request());

        $this->assertEquals('FooBundle:FooBar:index.html.twig', (string) $templateReference);
    }

    public function testGuessTemplateWithoutBundle()
    {
        $templateGuesser = new TemplateGuesser($this->kernel);
        $templateReference = $templateGuesser->guessTemplateName(array(
            new Fixture\Controller\OutOfBundleController(),
            'indexAction',
        ), new Request());

        $this->assertEquals(':OutOfBundle:index.html.twig', (string) $templateReference);
    }

    /**
     * @dataProvider controllerProvider
     */
    public function testGuessTemplateWithInvokeMagicMethod($controller, $patterns)
    {
        $templateGuesser = new TemplateGuesser($this->kernel, $patterns);

        $templateReference = $templateGuesser->guessTemplateName(array(
            $controller,
            '__invoke',
        ), new Request());

        $this->assertEquals('FooBundle::Foo.html.twig', (string) $templateReference);
    }

    /**
     * @dataProvider controllerProvider
     */
    public function testGuessTemplateWithACustomPattern($controller, $patterns)
    {
        $templateGuesser = new TemplateGuesser($this->kernel, $patterns);

        $templateReference = $templateGuesser->guessTemplateName(array(
            $controller,
            'indexAction',
        ), new Request());

        $this->assertEquals('FooBundle:Foo:index.html.twig', (string) $templateReference);
    }

    /**
     * @dataProvider controllerProvider
     */
    public function testGuessTemplateWithNotStandardMethodName($controller, $patterns)
    {
        $templateGuesser = new TemplateGuesser($this->kernel, $patterns);

        $templateReference = $templateGuesser->guessTemplateName(array(
            $controller,
            'fooBar',
        ), new Request());

        $this->assertEquals('FooBundle:Foo:fooBar.html.twig', (string) $templateReference);
    }

    public function controllerProvider()
    {
        return array(
            array(new Fixture\FooBundle\Controller\FooController(), array()),
            array(new Fixture\FooBundle\Action\FooAction(), array('/foobar/', '/FooBundle\\\Action\\\(.+)Action/')),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "stdClass" class does not look like a controller class (its FQN must match one of the following regexps: "/foo/", "/bar/"
     */
    public function testGuessTemplateWhenControllerFQNDoesNotMatchAPattern()
    {
        $this->kernel->getBundles();
        $templateGuesser = new TemplateGuesser($this->kernel, array('/foo/', '/bar/'));
        $templateReference = $templateGuesser->guessTemplateName(array(
            new \stdClass(),
            'indexAction',
        ), new Request());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage must be an array callable or an object defining the magic method __invoke. "object" given.
     */
    public function testInvalidController()
    {
        $this->kernel->getBundles();
        $templateGuesser = new TemplateGuesser($this->kernel);
        $templateReference = $templateGuesser->guessTemplateName(
            new Fixture\FooBundle\Controller\FooController(),
            new Request()
        );
    }

    protected function getBundle($name, $namespace, $parent = null)
    {
        $bundle = $this->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')->getMock();
        $bundle
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $bundle
            ->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValue($namespace));

        $bundle
            ->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue($parent));

        return $bundle;
    }
}

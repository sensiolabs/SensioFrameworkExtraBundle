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

        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
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

    protected function getBundle($name, $namespace, $parent = null)
    {
        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
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

<?php

namespace Sensio\Bundle\FrameworkExtraBundle\Tests\Request\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\CallbackParamConverter;
use Symfony\Component\DependencyInjection\Container;
use stdClass;

class CallbackParamConverterTest extends \PHPUnit_Framework_TestCase
{
    private $converter;
    private $container;
    private $callbackReturnMap;

    public function setUp()
    {
        $this->container         = new Container();
        $this->converter         = new CallbackParamConverter($this->container);
        $this->callbackReturnMap = array();
    }

    public function testSupports()
    {
        // Before adding any callbacks
        $config = $this->createConfiguration('Model\\Object');
        $this->assertFalse($this->converter->supports($config));

        // Add a lazy-load callback, does not actually verify service's method is callable,
        // since that would require non-lazy loading
        $this->container->set('object.callback', new stdClass());
        $this->converter->addService('Model\\Object', 'object.callback', 'bogus');

        $config = $this->createConfiguration('Model\\Object');
        $this->assertTrue($this->converter->supports($config));

        $this->converter->addCallback('Data\\Object', array($this, 'mockCallbackMethod'));

        $config = $this->createConfiguration('Data\\Object');
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration();
        $this->assertFalse($this->converter->supports($config));
    }

    public function testApply()
    {
        $id                           = rand(1, 1000);
        $this->callbackReturnMap[$id] = new stdClass();

        $request = new Request(array(), array(), array('id' => strval($id)));
        $config  = $this->createConfiguration('Model\\Object', 'model');

        $this->converter->addCallback('Model\\Object', array($this, 'mockCallbackMethod'));

        $this->converter->apply($request, $config);

        $this->assertInternalType('object', $request->attributes->get('model'));
        $this->assertTrue(
            $this->callbackReturnMap[$id] === $request->attributes->get('model'),
            'Returned a different instance'
        );
    }

    public function testApplyWithNotFound404Exception()
    {
        $id = rand(1, 1000);

        $request = new Request(array(), array(), array('id' => strval($id)));
        $config  = $this->createConfiguration('Model\\Object', 'model');

        $this->converter->addCallback('Model\\Object', array($this, 'mockCallbackMethod'));

        $this->setExpectedException(
            'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException',
            'Model\\Object object not found'
        );
        $this->converter->apply($request, $config);
    }

    public function createConfiguration($class = null, $name = null, $options = array())
    {
        $config = $this->getMock(
            'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ParamConverter', array(
            'getClass', 'getAliasName', 'getOptions', 'getName',
        ), array($options));
        if ($name !== null) {
            $config->expects($this->any())
                   ->method('getName')
                   ->will($this->returnValue($name));
        }
        if ($class !== null) {
            $config->expects($this->any())
                   ->method('getClass')
                   ->will($this->returnValue($class));
        }
        if ($options !== null) {
            $config->expects($this->any())
                   ->method('getOptions')
                   ->will($this->returnValue($options));
        }

        return $config;
    }

    /**
     * A method to allow $this to be a "callback"
     */
    public function mockCallbackMethod($id)
    {
        if (!isset($this->callbackReturnMap[$id])) {
            return null;
        }
        return $this->callbackReturnMap[$id];
    }
}

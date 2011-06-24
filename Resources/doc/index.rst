SensioFrameworkExtraBundle
==========================

The default Symfony2 ``FrameworkBundle`` implements a basic but robust and
flexible MVC framework. `SensioFrameworkExtraBundle`_ extends it to add sweet
conventions and annotations. It allows for more concise controllers.

Installation
------------

`Download`_ the bundle and put it under the ``Sensio\Bundle\`` namespace.
Then, like for any other bundle, include it in your Kernel class::

    public function registerBundles()
    {
        $bundles = array(
            ...

            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
        );

        ...
    }

Configuration
-------------

To enable all features provided by the bundle, add the following to your
configuration:

.. configuration-block::

    .. code-block:: yaml

        sensio_framework_extra: ~

    .. code-block:: xml

        <!-- xmlns:sensio-framework-extra="http://www.symfony-project.org/schema/dic/sensio-framework-extra" -->
        <sensio-framework-extra:config />

    .. code-block:: php

        // load the profiler
        $container->loadFromExtension('sensio_framework_extra', array(
        ));

You can disable some annotations and conventions by defining one or more
settings:

.. configuration-block::

    .. code-block:: yaml

        sensio_framework_extra:
            router:  { annotations: false }
            request: { converters: false }
            view:    { annotations: false, manage_null_arguments: false }
            cache:   { annotations: false }

    .. code-block:: xml

        <!-- xmlns:sensio-framework-extra="http://www.symfony-project.org/schema/dic/sensio-framework-extra" -->
        <sensio-framework-extra:config>
            <router annotations="false" />
            <request converters="false" />
            <view annotations="false" manage-null-arguments="false" />
            <cache converters="false" />
        </sensio-framework-extra:config>

    .. code-block:: php

        // load the profiler
        $container->loadFromExtension('sensio_framework_extra', array(
            'router'  => array('annotations' => false),
            'request' => array('converters' => false),
            'view'    => array('converters' => false, 'manage_null_arguments' => false),
            'cache'   => array('converters' => false),
        ));

Annotations for Controllers
---------------------------

Annotations are a great way to easily configure your controllers, from the
routes to the cache configuration.

Even if annotations are not a native feature of PHP, it still has several
advantages over the classic Symfony2 configuration methods:

* Code and configuration are in the same place (the controller class);
* Simple to learn and to use;
* Concise to write;
* Makes your Controller thin (as its sole responsibility is to get data from
  the Model).

.. tip::

   If you use view classes, annotations are a great way to avoid creating
   view classes for simple and common use cases.

The following annotations are defined by the bundle:

.. toctree::
   :maxdepth: 1

   annotations/routing
   annotations/converters
   annotations/view
   annotations/cache

This example shows all the available annotations in action::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

    /**
     * @Route("/blog")
     * @Cache(expires="tomorrow")
     */
    class AnnotController extends Controller
    {
        /**
         * @Route("/")
         * @Template
         */
        public function indexAction()
        {
            $posts = ...;

            return array('posts' => $posts);
        }

        /**
         * @Route("/{id}")
         * @ParamConverter("post", class="SensioBlogBundle:Post")
         * @Template("SensioBlogBundle:Annot:post", vars={"post"})
         * @Cache(smaxage="15")
         */
        public function showAction(Post $post)
        {
        }
    }

As the ``showAction`` method follows some conventions, you can omit some
annotations::

    /**
     * @Route("/{id}")
     * @Cache(smaxage="15")
     */
    public function showAction(Post $post)
    {
    }

.. _`SensioFrameworkExtraBundle`: https://github.com/sensio/SensioFrameworkExtraBundle
.. _`Download`: http://github.com/sensio/SensioFrameworkExtraBundle

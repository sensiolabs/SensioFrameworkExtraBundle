SensioFrameworkExtraBundle
==========================

The default Symfony ``FrameworkBundle`` implements a basic but robust and
flexible MVC framework. `SensioFrameworkExtraBundle`_ extends it to add sweet
conventions and annotations. It allows for more concise controllers.

Installation
------------

Before using this bundle in your project, add it to your ``composer.json`` file:

.. code-block:: bash

    $ composer require sensio/framework-extra-bundle

Then, like for any other bundle, include it in your Kernel class::

    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
        );

        // ...
    }

.. _release-cycle-note:

.. note::

    Since SensioFrameworkExtraBundle 3.0 its release cycle is out of sync
    with Symfony's release cycle. This means that you can simply require
    ``sensio/framework-extra-bundle: ~3.0`` in your ``composer.json`` file
    and Composer will automatically pick the latest bundle version for you.
    You have to use Symfony 2.3 or later for this workflow. Before Symfony
    2.3, the required version of the SensioFrameworkExtraBundle should be
    the same as your Symfony version.

If you plan to use or create annotations for controllers, make sure to update
your ``autoload.php`` by adding the following line::

    Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

Configuration
-------------

All features provided by the bundle are enabled by default when the bundle is
registered in your Kernel class.

The default configuration is as follow:

.. configuration-block::

    .. code-block:: yaml

        sensio_framework_extra:
            router:      { annotations: true }
            request:     { converters: true, auto_convert: true }
            view:        { annotations: true }
            cache:       { annotations: true }
            security:    { annotations: true }
            psr_message: { enabled: false } # Defaults to true if the PSR-7 bridge is installed


    .. code-block:: xml

        <!-- xmlns:sensio-framework-extra="http://symfony.com/schema/dic/symfony_extra" -->
        <sensio-framework-extra:config>
            <router annotations="true" />
            <request converters="true" auto_convert="true" />
            <view annotations="true" />
            <cache annotations="true" />
            <security annotations="true" />
            <psr-message enabled="false" /> <!-- Defaults to true if the PSR-7 bridge is installed -->
        </sensio-framework-extra:config>

    .. code-block:: php

        // load the profiler
        $container->loadFromExtension('sensio_framework_extra', array(
            'router'      => array('annotations' => true),
            'request'     => array('converters' => true, 'auto_convert' => true),
            'view'        => array('annotations' => true),
            'cache'       => array('annotations' => true),
            'security'    => array('annotations' => true),
            'psr_message' => array('enabled' => false), // Defaults to true if the PSR-7 bridge is installed
        ));

You can disable some annotations and conventions by defining one or more
settings to false.

Annotations for Controllers
---------------------------

Annotations are a great way to easily configure your controllers, from the
routes to the cache configuration.

Even if annotations are not a native feature of PHP, it still has several
advantages over the classic Symfony configuration methods:

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
   annotations/security

This example shows all the available annotations in action::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
         * @Method("GET")
         * @ParamConverter("post", class="SensioBlogBundle:Post")
         * @Template("SensioBlogBundle:Annot:show.html.twig", vars={"post"})
         * @Cache(smaxage="15", lastmodified="post.getUpdatedAt()", etag="'Post' ~ post.getId() ~ post.getUpdatedAt()")
         * @Security("has_role('ROLE_ADMIN') and is_granted('POST_SHOW', post)")
         */
        public function showAction(Post $post)
        {
        }
    }

As the ``showAction`` method follows some conventions, you can omit some
annotations::

    /**
     * @Route("/{id}")
     * @Cache(smaxage="15", lastModified="post.getUpdatedAt()", ETag="'Post' ~ post.getId() ~ post.getUpdatedAt()")
     * @Security("has_role('ROLE_ADMIN') and is_granted('POST_SHOW', post)")
     */
    public function showAction(Post $post)
    {
    }

The routes need to be imported to be active as any other routing resources, for
example:

.. code-block:: yaml

    # app/config/routing.yml

    # import routes from a controller directory
    annot:
        resource: "@AnnotRoutingBundle/Controller"
        type:     annotation

see :ref:`Annotated Routes Activation<frameworkextra-annotations-routing-activation>` for more details.

PSR-7 support
-------------

SensioFrameworkExtraBundle provides support for HTTP messages interfaces defined
in `PSR-7`_. It allows to inject instances of ``Psr\Http\Message\ServerRequestInterface``
and to return instances of ``Psr\Http\Message\ResponseInterface`` in controllers.

To enable this feature, `the HttpFoundation to PSR-7 bridge`_ and `Zend Diactoros`_ must be installed:

.. code-block:: bash

    $ composer require symfony/psr-http-message-bridge zendframework/zend-diactoros

Then, PSR-7 messages can be used directly in controllers like in the following code
snippet::

    namespace AppBundle\Controller;

    use Psr\Http\Message\ServerRequestInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Zend\Diactoros\Response;

    class DefaultController extends Controller
    {
        public function indexAction(ServerRequestInterface $request)
        {
            // Interact with the PSR-7 request

            $response = new Response();
            // Interact with the PSR-7 response

            return $response;
        }
    }

Note that internally, Symfony always use :class:`Symfony\\Component\\HttpFoundation\\Request`
and :class:`Symfony\\Component\\HttpFoundation\\Response` instances.

.. _`SensioFrameworkExtraBundle`: https://github.com/sensiolabs/SensioFrameworkExtraBundle
.. _`PSR-7`: http://www.php-fig.org/psr/psr-7/
.. _`the HttpFoundation to PSR-7 bridge`: https://github.com/symfony/psr-http-message-bridge
.. _`Zend Diactoros`: https://github.com/zendframework/zend-diactoros

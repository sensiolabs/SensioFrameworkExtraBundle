@Route and @Method
==================

Usage
-----

The @Route annotation maps a route pattern with a controller::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

    class PostController extends Controller
    {
        /**
         * @Route("/")
         */
        public function indexAction()
        {
            // ...
        }
    }

The ``index`` action of the ``Post`` controller is now mapped to the ``/``
URL. This is equivalent to the following YAML configuration:

.. code-block:: yaml

    blog_home:
        pattern:  /
        defaults: { _controller: SensioBlogBundle:Post:index }

Like any route pattern, you can define placeholders, requirements, and default
values::

    /**
     * @Route("/{id}", requirements={"id" = "\d+"}, defaults={"foo" = "bar"})
     */
    public function showAction($id)
    {
    }

You can also match more than one URL by defining additional ``@Route``
annotations::

    /**
     * @Route("/", defaults={"id" = 1})
     * @Route("/{id}")
     */
    public function showAction($id)
    {
    }

.. _frameworkextra-annotations-routing-activation:

Activation
----------

The routes need to be imported to be active as any other routing resources
(note the ``annotation`` type):

.. code-block:: yaml

    # app/config/routing.yml

    # import routes from a controller class
    post:
        resource: "@SensioBlogBundle/Controller/PostController.php"
        type:     annotation

You can also import a whole directory:

.. code-block:: yaml

    # import routes from a controller directory
    blog:
        resource: "@SensioBlogBundle/Controller"
        type:     annotation

As for any other resource, you can "mount" the routes under a given prefix:

.. code-block:: yaml

    post:
        resource: "@SensioBlogBundle/Controller/PostController.php"
        prefix:   /blog
        type:     annotation

Route Name
----------

A route defined with the ``@Route`` annotation is given a default name composed
of the bundle name, the controller name and the action name. That would be
``sensio_blog_post_index`` for the above example;

The ``name`` attribute can be used to override this default route name::

    /**
     * @Route("/", name="blog_home")
     */
    public function indexAction()
    {
        // ...
    }

Route Prefix
------------

A ``@Route`` annotation on a controller class defines a prefix for all action
routes::

    /**
     * @Route("/blog")
     */
    class PostController extends Controller
    {
        /**
         * @Route("/{id}")
         */
        public function showAction($id)
        {
        }
    }

The ``show`` action is now mapped to the ``/blog/{id}`` pattern.

Route Method
------------

There is a shortcut ``@Method`` annotation to specify the HTTP method allowed
for the route. To use it, import the ``Method`` annotation namespace::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

    /**
     * @Route("/blog")
     */
    class PostController extends Controller
    {
        /**
         * @Route("/edit/{id}")
         * @Method({"GET", "POST"})
         */
        public function editAction($id)
        {
        }
    }

The ``edit`` action is now mapped to the ``/blog/edit/{id}`` pattern if the HTTP
method used is either GET or POST.

The ``@Method`` annotation is only considered when an action is annotated with
``@Route``.

Controller as Service
---------------------

The ``@Route`` annotation on a controller class can also be used to assign the
controller class to a service so that the controller resolver will instantiate
the controller by fetching it from the DI container instead of calling ``new
PostController()`` itself::

    /**
     * @Route(service="my_post_controller_service")
     */
    class PostController extends Controller
    {
        // ...
    }

@extra:Route
============

Usage
-----

The @extra:Route annotation maps a route pattern with a controller::

    class PostController extends Controller
    {
        /**
         * @extra:Route("/")
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
     * @extra:Route("/{id}", requirements={"id" = "\d+"}, defaults={"foo" = "bar"})
     */
    public function showAction($id)
    {
    }

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

Or even import all controllers:

.. code-block:: yaml

    # import routes from all controllers
    all:
        resource: */Controller
        type:     annotation

As for any other resource, you can "mount" the routes under a given prefix:

.. code-block:: yaml

    post:
        resource: "@SensioBlogBundle/Controller/PostController.php"
        prefix:   /blog
        type:     annotation

Route Name
----------

By default, a route defined with the ``@extra:Route`` annotation is given a name
based on the controller class and method names:
``sensioblogbundle_controller_postcontroller_indexaction`` for the above example;
the ``name`` attribute overrides the generated route name::

    /**
     * @extra:Route("/", name="blog_home")
     */
    public function indexAction()
    {
        // ...
    }

Route Prefix
------------

A ``@extra:Route`` annotation on a controller class defines a prefix for all action
routes::

    /**
     * @extra:Route("/blog")
     */
    class PostController extends Controller
    {
        /**
         * @extra:Route("/{id}")
         */
        public function showAction($id)
        {
        }
    }

The ``show`` action is now mapped to the ``/blog/{id}`` pattern.

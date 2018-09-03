@Route and @Method
==================

**Routing annotations of the SensioFrameworkExtraBundle are deprecated** since
version 5.2 because they are now a core feature of Symfony.

How to Update your Applications
-------------------------------

``@Route`` Annotation
~~~~~~~~~~~~~~~~~~~~~

The Symfony annotation has the same options as the SensioFrameworkExtraBundle
annotation, so you only have to update the annotation class namespace:

.. code-block:: diff

    -use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    +use Symfony\Component\Routing\Annotation\Route;

    class DefaultController extends Controller
    {
        /**
         * @Route("/")
         */
        public function index()
        {
            // ...
        }
    }

``@Method`` Annotation
~~~~~~~~~~~~~~~~~~~~~~

The ``@Method`` annotation from SensioFrameworkExtraBundle has been removed.
Instead, the Symfony ``@Route`` annotation defines a new ``methods`` option to
restrict the HTTP methods of the route:

.. code-block:: diff

    -use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    -use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    +use Symfony\Component\Routing\Annotation\Route;

    class DefaultController extends Controller
    {
        /**
    -      * @Route("/show/{id}")
    -      * @Method({"GET", "HEAD"})
    +      * @Route("/show/{id}", methods={"GET","HEAD"})
         */
        public function show($id)
        {
            // ...
        }
    }

Read the `chapter about Routing`_ in the Symfony Documentation to learn
everything about these and the other annotations available.

.. _`chapter about Routing`: https://symfony.com/doc/current/routing.html

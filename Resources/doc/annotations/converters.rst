@extra:ParamConverter
=====================

Usage
-----

The ``@extra:ParamConverter`` annotation calls *converters* to convert request
parameters to objects. These objects are stored as request attributes and so
they can be injected as controller method arguments::

    /**
     * @extra:Route("/blog/:id")
     * @extra:ParamConverter("post", class="SensioBlogBundle:Post")
     */
    public function showAction(Post $post)
    {
    }

Several things happens under the hood:

* The converter tries to get a ``SensioBlogBundle:Post`` object from the request
  attributes (request attributes comes from route placeholders -- here
  ``id``);

* If no ``Post`` object is found, a ``404`` Response is generated;

* If a ``Post`` object is found, a new ``post`` request attribute is defined
  (accessible via ``$request->attributes->get('post')``);

* As for any other request attribute, it is automatically injected in the
  controller when present in the method signature.

If you use type hinting as in the example above, you can even omit the
``@extra:ParamConverter`` annotation altogether::

    // automatic with method signature
    public function showAction(Post $post)
    {
    }

Built-in Converters
-------------------

The bundle has only one built-in converter, the Doctrine one.

Creating a Converter
--------------------

All converters must implement the
:class:`Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\ParamConverterInterface`::

    namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
    use Symfony\Component\HttpFoundation\Request;

    interface ParamConverterInterface
    {
        function apply(Request $request, ConfigurationInterface $configuration);

        function supports(ConfigurationInterface $configuration);
    }

The ``supports()`` method must return ``true`` when it is able to convert the
given configuration (a ``ParamConverter`` instance).

The ``ParamConverter`` instance has three information about the annotation:

* ``name``: The attribute name;
* ``class``: The attribute class name (can be any string representing a class
  name);
* ``options``: An array of options

The ``apply()`` method is called whenever a configuration is supported. Based
on the request attributes, it should set an attribute named
``$configuration->getName()``, which stores an object of class
``$configuration->getClass()``.

.. tip::
   Use the ``DoctrineConverter`` class as a template for your own converters.

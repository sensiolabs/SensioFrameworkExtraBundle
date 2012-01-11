@ParamConverter
===============

Usage
-----

The ``@ParamConverter`` annotation calls *converters* to convert request
parameters to objects. These objects are stored as request attributes and so
they can be injected as controller *method* arguments::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

    /**
     * @Route("/blog/{id}")
     * @ParamConverter("post", class="SensioBlogBundle:Post")
     */
    public function showAction(Post $post)
    {
    }

Several things happens under the hood:

* The converter tries to get a ``SensioBlogBundle:Post`` object from the
  request attributes (request attributes comes from route placeholders -- here
  ``id``);

* If no ``Post`` object is found, a ``404`` Response is generated;

* If a ``Post`` object is found, a new ``post`` request attribute is defined
  (accessible via ``$request->attributes->get('post')``);

* As for any other request attribute, it is automatically injected in the
  controller when present in the method signature.

If you use type hinting as in the example above, you can even omit the
``@ParamConverter`` annotation altogether::

    // automatic with method signature
    public function showAction(Post $post)
    {
    }

Built-in Converters
-------------------

The bundle has only one built-in converter, the Doctrine one.

Doctrine Converter
~~~~~~~~~~~~~~~~~~

By default, the Doctrine converter uses the *default* entity manager. This can
be configured with the ``entity_manager`` option::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

    /**
     * @Route("/blog/{id}")
     * @ParamConverter("post", class="SensioBlogBundle:Post", options={"entity_manager" = "foo"})
     */
    public function showAction(Post $post)
    {
    }

The Doctrine converter maps urls in the following ways:

1. If you got a url with a parameter that is the name of the paramconverter in the url it will use
that as a parameter to the repository of the entitys `find()` method::
    
    /**
     * @Route("/blog/{post}")
     * @ParamConverter("post", class="SensioBlogBundle:Post")
     */
    public function showAction(Post $post)
    {
    }

    ... in the code:
    $id = $request->attributes->get("post");
    $this->registry->getRepository($class, $options['entity_manager'])->find($id);

2. If that does not work, then it will try to use other attributes in the route to find your entity::

    /**
     * @Route("/blog/{name}")
     * @ParamConverter("post", class="SensioBlogBundle:Post")
     */
    public function showAction(Post $post)
    {
    }
    
    ... in the code:
    $criteria = array ('name' =>  $request->attributes->get("name"));
    $this->registry->getRepository($class, $options['entity_manager'])->findOneBy($criteria);

3. If you want to use an explicit attribute to map the object you can use the object_attribute::

    /**
     * @Route("/blog/{post}")
     * @ParamConverter("post", class="SensioBlogBundle:Post", options={'object_attribute' : "name"})
     */
    public function showAction(Post $post)
    {
    }

    ... in the code:
    $value = $request->attributes->get("post");
    $critera = array('name' => $value);
    $this->registry->getRepository($class, $options['entity_manager'])->findOneBy($criteria);



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

.. tip::
   
   Adding this annotation to a class will fail silently.

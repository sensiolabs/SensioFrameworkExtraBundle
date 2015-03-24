@ParamConverter
===============

Usage
-----

The ``@ParamConverter`` annotation calls *converters* to convert request
parameters to objects. These objects are stored as request attributes and so
they can be injected as controller method arguments::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

    /**
     * @Route("/blog/{id}")
     * @ParamConverter("post", class="SensioBlogBundle:Post")
     */
    public function showAction(Post $post)
    {
    }

Several things happen under the hood:

* The converter tries to get a ``SensioBlogBundle:Post`` object from the
  request attributes (request attributes comes from route placeholders -- here
  ``id``);

* If no ``Post`` object is found, a ``404`` Response is generated;

* If a ``Post`` object is found, a new ``post`` request attribute is defined
  (accessible via ``$request->attributes->get('post')``);

* As for other request attributes, it is automatically injected in the
  controller when present in the method signature.

If you use type hinting as in the example above, you can even omit the
``@ParamConverter`` annotation altogether::

    // automatic with method signature
    public function showAction(Post $post)
    {
    }

.. tip::

    You can disable the auto-conversion of type-hinted method arguments feature
    by setting the ``auto_convert`` flag to ``false``:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            sensio_framework_extra:
                request:
                    converters: true
                    auto_convert: false

        .. code-block:: xml

            <sensio-framework-extra:config>
                <request converters="true" auto-convert="true" />
            </sensio-framework-extra:config>

To detect which converter is run on a parameter the following process is run:

* If an explicit converter choice was made with
  ``@ParamConverter(converter="name")`` the converter with the given name is
  chosen.

* Otherwise all registered parameter converters are iterated by priority. The
  ``supports()`` method is invoked to check if a param converter can convert
  the request into the required parameter. If it returns ``true`` the param
  converter is invoked.

Built-in Converters
-------------------

The bundle has two built-in converters, the Doctrine one and a DateTime
converter.

Doctrine Converter
~~~~~~~~~~~~~~~~~~

Converter Name: ``doctrine.orm``

The Doctrine Converter attempts to convert request attributes to Doctrine
entities fetched from the database. Two different approaches are possible:

- Fetch object by primary key.
- Fetch object by one or several fields which contain unique values in the
  database.

The following algorithm determines which operation will be performed.

- If an ``{id}`` parameter is present in the route, find object by primary key.
- If an option ``'id'`` is configured and matches route parameters, find object by primary key.
- If the previous rules do not apply, attempt to find one entity by matching
  route parameters to entity fields. You can control this process by
  configuring ``exclude`` parameters or a attribute to field name ``mapping``.

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

If the placeholder has not the same name as the primary key, pass the ``id``
option::

    /**
     * @Route("/blog/{post_id}")
     * @ParamConverter("post", class="SensioBlogBundle:Post", options={"id" = "post_id"})
     */
    public function showAction(Post $post)
    {
    }

.. tip::

   The ``id`` option specifies which placeholder from the route gets passed to the repository
   method used. If no repository method is specified, ``find()`` is used by default.

This also allows you to have multiple converters in one action::

    /**
     * @Route("/blog/{id}/comments/{comment_id}")
     * @ParamConverter("comment", class="SensioBlogBundle:Comment", options={"id" = "comment_id"})
     */
    public function showAction(Post $post, Comment $comment)
    {
    }

In the example above, the ``$post`` parameter is handled automatically, but ``$comment`` is
configured with the annotation since they can not both follow the default convention.

If you want to match an entity using multiple fields use the ``mapping`` hash
option: the key is route placeholder name and the value is the Doctrine
field name::

    /**
     * @Route("/blog/{date}/{slug}/comments/{comment_slug}")
     * @ParamConverter("post", options={"mapping": {"date": "date", "slug": "slug"}})
     * @ParamConverter("comment", options={"mapping": {"comment_slug": "slug"}})
     */
    public function showAction(Post $post, Comment $comment)
    {
    }

If you are matching an entity using several fields, but you want to exclude a
route parameter from being part of the criteria::

    /**
     * @Route("/blog/{date}/{slug}")
     * @ParamConverter("post", options={"exclude": {"date"}})
     */
    public function showAction(Post $post, \DateTime $date)
    {
    }

If you want to specify the repository method to use to find the entity (for example,
to add joins to the query), you can add the ``repository_method`` option::

    /**
     * @Route("/blog/{id}")
     * @ParamConverter("post", class="SensioBlogBundle:Post", options={"repository_method" = "findWithJoins"})
     */
    public function showAction(Post $post)
    {
    }

The specified repository method will be called with the criteria in an ``array``
as parameter. This is a good fit with Doctrine's ``findBy`` and ``findOneBy``
methods.

There are cases where you want to you use your own repository method and you
want to map the criteria to the method signature. This is possible when you set
the ``map_method_signature`` option to true. The default is false::

    /**
     * @Route("/user/{first_name}/{last_name}")
     * @ParamConverter("user", class="CtorsBlogBundle:User", options={
     *    "repository_method" = "findByFullName",
     *    "mapping": {"first_name": "firstName", "last_name": "lastName"},
     *    "map_method_signature" = true
     * })
     */
    public function showAction(User $user)
    {
    }

    class UserRepository
    {
        public function findByFullName($firstName, $lastName)
        {
            ...
        }
    }

.. tip::

   When ``map_method_signature`` is ``true``, the ``firstName`` and
   ``lastName`` parameters do not have to be Doctrine fields.

DateTime Converter
~~~~~~~~~~~~~~~~~~

Converter Name: ``datetime``

The datetime converter converts any route or request attribute into a datetime
instance::

    /**
     * @Route("/blog/archive/{start}/{end}")
     */
    public function archiveAction(\DateTime $start, \DateTime $end)
    {
    }

By default any date format that can be parsed by the ``DateTime`` constructor
is accepted. You can be stricter with input given through the options::

    /**
     * @Route("/blog/archive/{start}/{end}")
     * @ParamConverter("start", options={"format": "Y-m-d"})
     * @ParamConverter("end", options={"format": "Y-m-d"})
     */
    public function archiveAction(\DateTime $start, \DateTime $end)
    {
    }

Creating a Converter
--------------------

All converters must implement the ``ParamConverterInterface``::

    namespace Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
    use Symfony\Component\HttpFoundation\Request;

    interface ParamConverterInterface
    {
        function apply(Request $request, ParamConverter $configuration);

        function supports(ParamConverter $configuration);
    }

The ``supports()`` method must return ``true`` when it is able to convert the
given configuration (a ``ParamConverter`` instance).

The ``ParamConverter`` instance has three pieces of information about the annotation:

* ``name``: The attribute name;
* ``class``: The attribute class name (can be any string representing a class
  name);
* ``options``: An array of options.

The ``apply()`` method is called whenever a configuration is supported. Based
on the request attributes, it should set an attribute named
``$configuration->getName()``, which stores an object of class
``$configuration->getClass()``.

To register your converter service, you must add a tag to your service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            my_converter:
                class:        MyBundle\Request\ParamConverter\MyConverter
                tags:
                    - { name: request.param_converter, priority: -2, converter: my_converter }

    .. code-block:: xml

        <service id="my_converter" class="MyBundle\Request\ParamConverter\MyConverter">
            <tag name="request.param_converter" priority="-2" converter="my_converter" />
        </service>

You can register a converter by priority, by name (attribute "converter"), or
both. If you don't specify a priority or a name, the converter will be added to
the converter stack with a priority of ``0``. To explicitly disable the
registration by priority you have to set ``priority="false"`` in your tag
definition.

.. tip::

   If you would like to inject services or additional arguments into a custom
   param converter, the priority shouldn't be higher than ``1``. Otherwise, the
   service wouldn't be loaded.

.. tip::

   Use the ``DoctrineParamConverter`` class as a template for your own converters.

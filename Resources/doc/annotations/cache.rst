@extra:Cache
============

Usage
-----

The ``@extra:Cache`` annotation makes it easy to define HTTP caching::

    /**
     * @extra:Cache(expires="tomorrow")
     */
    public function indexAction()
    {
    }

You can also use the annotation on a class to define caching for all methods::

    /**
     * @extra:Cache(expires="tomorrow")
     */
    class BlogController extends Controller
    {
    }

When there is a conflict between the class configuration and the method
configuration, the latter overrides the former::

    /**
     * @extra:Cache(expires="tomorrow")
     */
    class BlogController extends Controller
    {
        /**
         * @extra:Cache(expires="+2 days")
         */
        public function indexAction()
        {
        }
    }

Attributes
----------

Here is a list of accepted attributes and their HTTP header equivalent:

==================================== ===============
Annotation                           Response Method
==================================== ===============
``@extra:Cache(expires="tomorrow")`` ``$response->setExpires()``
``@extra:Cache(smaxage="15")``       ``$response->setSharedMaxAge()``
``@extra:Cache(maxage="15")``        ``$response->setMaxAge()``
==================================== ===============

.. note::
   The ``expires`` attribute takes any valid date understood by the PHP
   ``strtotime()`` function.

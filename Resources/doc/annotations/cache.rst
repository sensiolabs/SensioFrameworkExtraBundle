@Cache
======

Usage
-----

The ``@Cache`` annotation makes it easy to define HTTP caching::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

    /**
     * @Cache(expires="tomorrow")
     */
    public function indexAction()
    {
    }

You can also use the annotation on a class to define caching for all methods::

    /**
     * @Cache(expires="tomorrow")
     */
    class BlogController extends Controller
    {
    }

When there is a conflict between the class configuration and the method
configuration, the latter overrides the former::

    /**
     * @Cache(expires="tomorrow")
     */
    class BlogController extends Controller
    {
        /**
         * @Cache(expires="+2 days")
         */
        public function indexAction()
        {
        }
    }

Attributes
----------

Here is a list of accepted attributes and their HTTP header equivalent:

============================== ===============
Annotation                     Response Method
============================== ===============
``@Cache(expires="tomorrow")`` ``$response->setExpires()``
``@Cache(smaxage="15")``       ``$response->setSharedMaxAge()``
``@Cache(maxage="15")``        ``$response->setMaxAge()``
``@Cache(vary=["Cookie"])``    ``$response->setVary()``
============================== ===============

.. note::

   The ``expires`` attribute takes any valid date understood by the PHP
   ``strtotime()`` function.

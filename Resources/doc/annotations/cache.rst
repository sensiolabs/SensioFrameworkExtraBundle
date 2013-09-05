@Cache
======

The ``@Cache`` annotation makes it easy to define HTTP caching headers for
expiration and validation.

HTTP Expiration Strategies
--------------------------

The ``@Cache`` annotation makes it easy to define HTTP caching::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

    /**
     * @Cache(expires="tomorrow", public="true")
     */
    public function indexAction()
    {
    }

You can also use the annotation on a class to define caching for all actions
of a controller::

    /**
     * @Cache(expires="tomorrow", public="true")
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

HTTP Validation Strategies
--------------------------

The ``lastModified`` attribute adds a ``Last-Modified`` header to Responses
and automatically returns a 304 response when the response is not modified
based on the value of the ``If-Modified-Since`` Request header::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

    /**
     * @Cache(lastModified="post.getUpdatedAt()")
     */
    public function indexAction(Post $post)
    {
        // your code
    }

It's doing the same as the following code::

    public function myAction(Request $request, Post $post)
    {
        $response = new Response();
        $response->setLastModified($post->getUpdatedAt());
        if ($response->isNotModified($request)) {
            return $response;
        }

        // your code
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
``@Cache(public="true")``      ``$response->setPublic()``
============================== ===============

.. note::

   The ``expires`` attribute takes any valid date understood by the PHP
   ``strtotime()`` function.

@Cache
======

The ``@Cache`` annotation makes it easy to define HTTP caching headers for
expiration and validation.

HTTP Expiration Strategies
--------------------------

The ``@Cache`` annotation makes it easy to define HTTP caching::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

    /**
     * @Cache(expires="tomorrow", public=true)
     */
    public function indexAction()
    {
    }

You can also use the annotation on a class to define caching for all actions
of a controller::

    /**
     * @Cache(expires="tomorrow", public=true)
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

.. note::

   The ``expires`` attribute takes any valid date understood by the PHP
   ``strtotime()`` function.

HTTP Validation Strategies
--------------------------

The ``lastModified`` and ``ETag`` attributes manage the HTTP validation cache
headers. ``lastModified`` adds a ``Last-Modified`` header to Responses and
``ETag`` adds an ``ETag`` header.

Both automatically trigger the logic to return a 304 response when the
response is not modified (in this case, the controller is **not** called)::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

    /**
     * @Cache(lastModified="post.getUpdatedAt()", ETag="'Post' ~ post.getId() ~ post.getUpdatedAt().getTimestamp()")
     */
    public function indexAction(Post $post)
    {
        // your code
        // won't be called in case of a 304
    }

It's roughly doing the same as the following code::

    public function myAction(Request $request, Post $post)
    {
        $response = new Response();
        $response->setLastModified($post->getUpdatedAt());
        if ($response->isNotModified($request)) {
            return $response;
        }

        // your code
    }

.. note::

    The ETag HTTP header value is the result of the expression hashed with the
    ``sha256`` algorithm.

Attributes
----------

Here is a list of accepted attributes and their HTTP header equivalent:

======================================================================= ================================
Annotation                                                              Response Method
======================================================================= ================================
``@Cache(expires="tomorrow")``                                          ``$response->setExpires()``
``@Cache(smaxage="15")``                                                ``$response->setSharedMaxAge()``
``@Cache(maxage="15")``                                                 ``$response->setMaxAge()``
``@Cache(vary={"Cookie"})``                                             ``$response->setVary()``
``@Cache(public=true)``                                                 ``$response->setPublic()``
``@Cache(lastModified="post.getUpdatedAt()")``                          ``$response->setLastModified()``
``@Cache(ETag="post.getId() ~ post.getUpdatedAt().getTimestamp()")``    ``$response->setETag()``
======================================================================= ================================

@LastModified
=============

Usage
-----

The ``@LastModified`` annotation adds a ``Last-Modified`` header to Responses
and automatically returns a 304 response when the response is not modified
based on the value of the ``If-Modified-Since`` Request header::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

    /**
     * @LastModified("post.getUpdatedAt()")
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

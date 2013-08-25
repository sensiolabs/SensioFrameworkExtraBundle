@Cache
======

Usage
-----

The ``@LastModified`` annotation return not-modified 304 response when main parameter in request is not modified.::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

    /**
     * @ParamConverter("post", class="SensioBlogBundle:Post")
     * @LastModified(param="post", method="getUpdatedAt")
     */
    public function indexAction()
    {
    }

It doing the same as ::

    public function myAction(Request $request, Post $post)
    {
        $response = new Response();
        $response->setLastModified($post->getUpdatedAt());
        if ($response->isNotModified($request)) {
            return $response;
        }

        // ...
    }


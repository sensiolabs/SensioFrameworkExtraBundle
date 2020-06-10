@ContentSafe
============

User agents can ask for "safe" content by adding a `Prefer: safe` request header. The ``@ContentSafe`` annotation
automates defining the HTTP response headers related to the safe HTTP preference.

.. note::

   The :class:`Symfony\\Component\\HttpFoundation\\Response` will only be marked as safe when the :class:`Symfony\\Component\\HttpFoundation\\Request`
   is considered safe. Symfony considers a request as safe with https as protocol or when the request originates
   from a trusted proxy.

See `RFC 8674`_ for in-dept detail about the specification.

Annotating a controller action
------------------------------

When an action is annotated as ``@ContentSafe`` the eventual response headers will behave according to the request preferences::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ContentSafe;

    /**
     * @ContentSafe()
     */
    public function index()
    {
        return new Response(200, '<html><body>ok</body></html>');
    }

Annotating actions as ``@ContentSafe`` especially comes in handy when the :class:`Symfony\\Component\\HttpFoundation\\Response`
is created outside of the scope of the controller action. As example, in combination with the ``@Template`` annotation::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ContentSafe;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    /**
     * @ContentSafe()
     * @Template()
     */
    public function index()
    {
        return ['data' => 'for-template'];
    }

.. note::

   The ``@ContentSafe`` annotation will automatically become available when at least symfony/http-foundation v5.2 is
   is available.

.. _`RFC 8674`: https://tools.ietf.org/html/rfc8674

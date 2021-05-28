@Template
=========

Usage
-----

The ``@Template`` annotation associates a controller with a template name:

.. configuration-block::

    .. code-block:: php-annotations

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

        /**
         * @Template("@SensioBlog/post/show.html.twig")
         */
        public function show($id)
        {
            // get the Post
            $post = ...;

            return array('post' => $post);
        }

    .. code-block:: php-attributes

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

        #[Template('@SensioBlog/post/show.html.twig')]
        public function show($id)
        {
            // get the Post
            $post = ...;

            return array('post' => $post);
        }

When using the ``@Template`` annotation, the controller should return an
array of parameters to pass to the view instead of a ``Response`` object.

.. note::

    If you want to stream your template, you can make it with the following configuration:

    .. configuration-block::

        .. code-block:: php-annotations

            /**
             * @Template(isStreamable=true)
             */
            public function show($id)
            {
                // ...
            }

        .. code-block:: php-attributes

            #[Template(isStreamable: true)]
            public function show($id)
            {
                // ...
            }

.. tip::

   If the action returns a ``Response`` object, the ``@Template`` annotation is
   simply ignored.

If the template is named after the controller and action names, which is the
case for the above example, you can even omit the annotation value:

.. configuration-block::

    .. code-block:: php-annotations

        /**
         * @Template
         */
        public function show($id)
        {
            // get the Post
            $post = ...;

            return array('post' => $post);
        }

    .. code-block:: php-attributes

        #[Template]
        public function show($id)
        {
            // get the Post
            $post = ...;

            return array('post' => $post);
        }

.. tip::

   Sub-namespaces are converted into underscores. The
   ``Sensio\BlogBundle\Controller\UserProfileController::showDetails()`` action
   will resolve to ``@SensioBlog/user_profile/show_details.html.twig``

And if the only parameters to pass to the template are method arguments, you
can use the ``vars`` attribute instead of returning an array. This is very
useful in combination with the ``@ParamConverter`` :doc:`annotation
<converters>`:

.. configuration-block::

    .. code-block:: php-annotations

        /**
         * @ParamConverter("post", class="SensioBlogBundle:Post")
         * @Template("@SensioBlog/post/show.html.twig", vars={"post"})
         */
        public function show(Post $post)
        {
        }

    .. code-block:: php-attributes

        #[ParamConverter('post', class: 'SensioBlogBundle:Post')]
        #[Template('@SensioBlog/post/show.html.twig"', vars: ['post'])]
        public function show(Post $post)
        {
        }

which, thanks to conventions, is equivalent to the following configuration:

.. configuration-block::

    .. code-block:: php-annotations

        /**
         * @Template(vars={"post"})
         */
        public function show(Post $post)
        {
        }

    .. code-block:: php-attributes

        #[Template(vars: ['post'])]
        public function show(Post $post)
        {
        }

You can make it even more concise as all method arguments are automatically
passed to the template if the method returns ``null`` and no ``vars`` attribute
is defined:

.. configuration-block::

    .. code-block:: php-annotations

        /**
         * @Template
         */
        public function show(Post $post)
        {
        }

    .. code-block:: php-attributes

        #[Template]
        public function show(Post $post)
        {
        }

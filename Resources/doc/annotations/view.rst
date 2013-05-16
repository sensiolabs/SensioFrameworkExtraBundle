@Template
=========

Usage
-----

The ``@Template`` annotation associates a controller with a template name::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    /**
     * @Template("SensioBlogBundle:Post:show.html.twig")
     */
    public function showAction($id)
    {
        // get the Post
        $post = ...;

        return array('post' => $post);
    }

When using the ``@Template`` annotation, the controller should return an
array of parameters to pass to the view instead of a ``Response`` object.

.. note::

    If you want to stream your template, you can make it with the following configuration::

        /**
         * @Template(isStreamable=true)
         */
        public function showAction($id)
        {
            // ...
        }


.. tip::
   If the action returns a ``Response`` object, the ``@Template`` 
   annotation is simply ignored.

If the template is named after the controller and action names, which is the
case for the above example, you can even omit the annotation value::

    /**
     * @Template
     */
    public function showAction($id)
    {
        // get the Post
        $post = ...;

        return array('post' => $post);
    }

.. note::

    If you are using PHP as a templating system, you need to make it
    explicit::

        /**
         * @Template(engine="php")
         */
        public function showAction($id)
        {
            // ...
        }

And if the only parameters to pass to the template are method arguments, you
can use the ``vars`` attribute instead of returning an array. This is very
useful in combination with the ``@ParamConverter`` :doc:`annotation
<converters>`::

    /**
     * @ParamConverter("post", class="SensioBlogBundle:Post")
     * @Template("SensioBlogBundle:Post:show.html.twig", vars={"post"})
     */
    public function showAction(Post $post)
    {
    }

which, thanks to conventions, is equivalent to the following configuration::

    /**
     * @Template(vars={"post"})
     */
    public function showAction(Post $post)
    {
    }

You can make it even more concise as all method arguments are automatically
passed to the template if the method returns ``null`` and no ``vars``
attribute is defined::

    /**
     * @Template
     */
    public function showAction(Post $post)
    {
    }

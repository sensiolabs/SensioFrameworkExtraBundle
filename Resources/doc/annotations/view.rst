@extra:Template
===============

Usage
-----

The ``@extra:Template`` annotation associates a controller with a template name::

    /**
     * @extra:Template("SensioBlogBundle:Post:show")
     */
    public function showAction($id)
    {
        // get the Post
        $post = ...;

        return array('post' => $post);
    }

When using the ``@extra:Template`` annotation, the controller should return an
array of parameters to pass to the view instead of a ``Response`` object.

.. tip::
   If the action returns a ``Response`` object, the ``@extra:Template`` 
   annotation is simply ignored.

If the template is named after the controller and action names, which is the
case for the above example, you can even omit the annotation value::

    /**
     * @extra:Template
     */
    public function showAction($id)
    {
        // get the Post
        $post = ...;

        return array('post' => $post);
    }

And if the only parameters to pass to the template are method arguments, you
can use the ``vars`` attribute instead of returning an array. This is very
useful in combination with the ``@extra:ParamConverter`` :doc:`annotation
<converters>`::

    /**
     * @extra:ParamConverter("post", class="SensioBlogBundle:Post")
     * @extra:Template("SensioBlogBundle:Post:show", vars={"post"})
     */
    public function showAction(Post $post)
    {
    }

which, thanks to conventions, is equivalent to the following configuration::

    /**
     * @extra:Template(vars={"post"})
     */
    public function showAction(Post $post)
    {
    }

You can make it even more concise as all method arguments are automatically
passed to the template if the method returns ``null`` and no ``vars``
attribute is defined::

    /**
     * @extra:Template
     */
    public function showAction(Post $post)
    {
    }

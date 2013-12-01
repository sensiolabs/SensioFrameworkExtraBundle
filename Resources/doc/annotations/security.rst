@Security
=========

Usage
-----

The ``@Security`` annotation restricts access on controllers::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

    class PostController extends Controller
    {
        /**
         * @Security("has_role('ROLE_ADMIN')")
         */
        public function indexAction()
        {
            // ...
        }
    }

The expression can use all functions that you can use in the ``access_control``
section of the security bundle configuration, with the addition of the
``is_granted()`` function.

The expression has access to the following variables:

* ``token``: The current security token;
* ``user``: The current user object;
* ``request``: The request instance;
* ``roles``: The user roles;
* and all request attributes.

The ``is_granted()`` function allows you to restrict access based on variables
passed to the controller::

    /**
     * @Security("is_granted('POST_SHOW', post)")
     */
    public function showAction(Post $post)
    {
    }

.. note::

    Defining a ``Security`` annotation has the same effect as defining an
    access control rule, but it is more efficient as the check is only done
    when this specific route is accessed.

.. tip::

    You can also add a ``@Security`` annotation on a controller class.

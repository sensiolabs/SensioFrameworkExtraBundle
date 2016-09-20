@Csrf
=========

Usage
-----

The ``@Csrf`` annotation validates csrf-token before executing action::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Csrf;

    class PostsController extends Controller
    {
        /**
         * @Csrf("posts_delete")
         */
        public function batchDeleteAction()
        {
            // ...
        }
    }

On the template side

.. code-block:: html+twig
    <form action="{{ path('acme_posts_batch_delete') }}" method="POST">
        <input type="checkbox" name="posts[]" value="1" /> 1<br />
        <input type="checkbox" name="posts[]" value="2" /> 2<br />
        <input type="checkbox" name="posts[]" value="3" /> 3<br />

        <input type="hidden" name="_token" value="{{ csrf_token('posts_delete') }}" />

        <input type="submit" value="Delete selected posts" />
    </form>

You can also specify request parameter from which need retrieve token (default is ``_token``)::

    /**
     * @Csrf(intention="posts_delete", param="_posts_csrf_token")
     */
    public function batchDeleteAction()
    {
        // ...
    }

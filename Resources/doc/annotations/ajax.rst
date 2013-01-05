@AJAX
======

Usage
-----

The ``@AJAX`` annotation defines an action as "AJAX only"::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\AJAX;

    /**
     * @AJAX
     */
    public function indexAction()
    {
    }

This way you can avoid checking if the request is an XMLHttpRequest in every 
AJAX action. It works if your JavaScript library set an X-Requested-With HTTP 
header. It is known to work with Prototype, Mootools and jQuery.

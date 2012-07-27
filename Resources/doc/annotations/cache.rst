@Cache
======

Usage
-----

The ``@Cache`` annotation makes it easy to define HTTP caching::

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

    /**
     * @Cache(expires="tomorrow")
     */
    public function indexAction()
    {
    }

Attributes
----------

Here is a list of accepted attributes and their HTTP header equivalent:

============================== ===============
Annotation                     Response Method
============================== ===============
``@Cache(expires="tomorrow")`` ``$response->setExpires()``
``@Cache(smaxage="15")``       ``$response->setSharedMaxAge()``
``@Cache(maxage="15")``        ``$response->setMaxAge()``
============================== ===============

.. note::

   The ``expires`` attribute takes any valid date understood by the PHP
   ``strtotime()`` function.

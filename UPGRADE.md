UPGRADE FROM 2.0 to 2.1
=======================

### DoctrineParamConverter: Request Attributes with same name as Arguments

Previously the DoctrineParamConverter defaulted to finding objects by 'id'
parameter. This is unintuitive and is now overwritten by a behavior where
the request attributes with the same name as entity arguments is matched
with higher priority.

This might cause problems if you are using this parameter for another object conversion.

### DoctrineParamConverter with multiple Arguments may clash

In 2.0 the parameter converter matched only entity fields against route parameters.
With 2.1, the matching now also includes single-valued associations. Depending
on fields in entities this might lead to clashes when you update to the latest version.

Example that may break with the latest (2.1) version:

    /**
     * @Route("/user/{email}/{address}")
     * @ParamConverter("address", class="MyBundle:Address", options={"id": "address"})
     */
    public function showAction(User $user, Address $address)
    {
    }

    class User
    {
        /** @ORM\Column(type="string") */
        public $email;
        /** @ORM\ManyToOne(targetEntity="Address") */
        public $address;
    }

Since address exists as field in `User` and User is not searched by primary key but
by field, this scenario now adds `address` to the criteria to find a user instance.
In scenarios of related entities this might even (just) work, but you never know.

You can fix this by configuring explicit mapping for `User`:

    /**
     * @Route("/user/{email}/{address}")
     * @ParamConverter("address", options={"id": "address"})
     * @ParamConverter("email", options={"exclude": ["address"]})
     */
    public function showAction(User $user, Address $address)
    {
    }

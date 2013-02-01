CHANGELOG
=========

2.2
---

 * added the possibility to configure the repository method to use for the
   Doctrine converter via the repository_method option.
 * [BC break] Added the possibility to configure the default templating engine
   in a configuration file.

2.1
---

 * added the possibility to configure the id name for the Doctrine converter via the id option
 * [BC break] The ParamConverterInterface::apply() method now must return a
   Boolean value indicating if a conversion was done.

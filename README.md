PositibeCmfBundle [deprecated]
==============================

This bundle is now deprecated in favor to PositibeCoreBundle[https://github.com/Positibe/CoreBundle]

This bundle provide combine some Positibe bundles with Sylius ResourcesBundle to provide a simple CMS for Symfony projects.

Installation
------------

To install the bundle just add the dependent bundles:

    php composer.phar require positibe/cmf-bundle

You must see the configuration of:
* PositibeOrmSeoBundle
* PositibeOrmMenuBundle
* PositibeOrmBlockBundle
* PositibeOrmMediaBundle
* PositibeOrmRoutingBundle
* PositibeOrmContentBundle

Next, be sure to enable the bundles in your application kernel:

    <?php
    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            // All dependencies of all Positibe bundles
            new Positibe\Bundle\OrmMenuBundle\PositibeOrmMenuBundle(),

            // ...
        );
    }

Documentation
-------------

En Español
~~~~~~~~~~

Ver la documentación en /path/to/bundle/Resources/doc/es/index.md

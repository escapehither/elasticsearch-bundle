Escape Hither SearchManagerBundle
===============================

Step 1: Download the Bundle
---------------------------


Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require escapehither/search-manager-bundle dev-master
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
             new EscapeHither\SearchManagerBundle\EscapeHitherSearchManagerBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Create your Resource class
-------------------------
This bundle use [The Symfony Serializer Component.](https://symfony.com/doc/current/components/serializer.html)
Suppose you have a a resource class, add Annotation groups.
Only attributes in the groups index will be normalize.



```php
namespace Acme;

use Symfony\Component\Serializer\Annotation\Groups;

class Product
{
    /**
     * @Groups({"index"})
     */
    public $foo;

    /**
     * @Groups({"index"})
     */
    public function getBar() // is* methods are also supported
    {
        return $this->bar;
    }

    // ...
}
```

Step 4: Import and define configuration
-------------------------

1. Import config file in `app/config/config.yml` for default filter set configuration:

    ```yaml
    imports:
       - { resource: "@EscapeHitherSearchManagerBundle/Resources/config/services.yml" }
       - { resource: "@EscapeHitherSearchManagerBundle/Resources/config/config.yml" }
    ```

2. Import routing files in `app/config/routing.yml`:

    ```yaml
    escape_hither_security_manager:
        resource: "@EscapeHitherSecurityManagerBundle/Resources/config/routing.yml"
        prefix:   /
    ```

3. Configuration reference:
If you want to index your resource, add in your config file.

    ```yaml
   escape_hither_search_manager:
       indexes:
           product:
               entity: Acme\Product
               index_name: product
               type: product
               tags:
                   categories:
                       include: ['id','code','name']
    ```

4:  Index all (Next feature)
-------------------------
Every time you add new field, Index all document. This command will delete and rebuild all defined index under escape_hither_search_manager indexes configuration.
```console
$ bin/console cache:clear -e prod
$ bin/console cache:clear
$ bin/console escapehither:searchmanager:index-all
```

5. Add a search page:
-------------------------
If you want create a new search route. in your routing.yml just add you new route like this.

```yaml
   genia_search:
       path:     /search
       defaults:
           _controller: "EscapeHitherSearchManagerBundle:Default:search"
           template: OpenMarketPlaceSearchManagerBundle:Default:index.html.twig
           index :
               name: open-market-place
               type: product
           pagination:
               size: 10
       methods:  GET

```
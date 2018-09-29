Escape Hither SearchManagerBundle
===============================

This bundle provide an integration for the official elastic search php.

Versions & Dependencies
-----------------------
Compatible with Elasticsearch 5 and 6. It requires Symfony 3 or 4. When using

The following table shows the compatibilities of different versions of the bundle.

| Es search Manager                                                                       | Elastic Seach php | Elasticsearch | Symfony    | PHP   |
| --------------------------------------------------------------------------------------- | ------------------| ------------- | ---------- | ----- |
| [5.x]                                                                                   | ^5.2\|^6          | 5.\*\|6.\*    | ^3.2\|^4   | >=5.6 |

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
use EscapeHither\SearchManagerBundle\Entity\IndexableEntityInterface;

class Product implements IndexableEntityInterface
{
    /**
     * @Groups({"index"})
     */
    public $bar;

    /**
     * @var \DateTime $updatedAt
     */
    public $updatedAt;

    /**
     * @Groups({"index"})
     */
    public function getBar() // is* methods are also supported
    {
        return $this->bar;
    }
    /**
     * This method tells doctrine to always track this entity.
     */
    public function trackMe(){
        $this->updatedAt = new \DateTime('now');
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
    host: es
    indexes:
        product:
            entity: OpenMarketPlace\ProductManagerBundle\Entity\Product
            index_name: open-market-place
            type: product
            facets: 
                tags:
                    categories:
                        include: ['id','code','name']
                tags_relation:
                    offer.seller.id:
                        entity: OpenMarketPlace\UserManagerBundle\Entity\Seller
                        index_name: open-market-place
                        type: seller
                        field_name: commercialName
                        display_name: Seller
                        tag_type: terms  
                dates:
                    createdAt:
                        field_name: createdAt
                        display_name: date one
                        tag_type: terms
                    updatedAt:
                        field_name: updatedAt
                        display_name: date two
                        tag_type: terms
                ranges:
                    createddAt:
                        field_name: createddAt
                        display_name: range one
                        tag_type: date
                    updateddAt:
                        field_name: updateddAt
                        display_name: range two
                        tag_type: date
                    masterVariant.price:
                        field_name: masterVariant.price
                        display_name: price
                        tag_type: price
    ```

4:  Index all content
---------------------
Every time you add new field, Index all document. This command will delete and rebuild all all content according to the index provided.
```console
$ bin/console cache:clear -e prod
$ bin/console cache:clear
$ bin/console escapehither:esm:index:all
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
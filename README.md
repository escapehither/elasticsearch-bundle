Escape Hither SearchManagerBundle
===============================

Step 1: Download the Bundle
---------------------------
The Bundle is actually in a private Repository.
In your Composer.json add:
```json
{
  //....
  "repositories": [{
    "type": "composer",
    "url": "https://packages.escapehither.com"
  }]

}
```
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
             new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
             new Knp\Bundle\MenuBundle\KnpMenuBundle(),
             new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
             new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Create your User class
-------------------------
Suppose you have a bundle name appBundle

<?php
namespace AppBundle\Entity;
use EscapeHither\SecurityManagerBundle\Entity\User as BaseUser;

```php
class User extends BaseUser {
    private $id;
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
```
```xml
<?xml version="1.0" encoding="utf-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
    <entity name="AppBundle\Entity\User" table="user_account">
        <id name="id" type="integer" column="id">
            <generator strategy="IDENTITY"/>
        </id>
    </entity>
</doctrine-mapping>

```


Step 4: Import and define configuration
-------------------------

1. Import config file in `app/config/config.yml` for default filter set configuration:

    ```yaml
    imports:
       - { resource: "@EscapeHitherSearchManagerBundle/Resources/config/services.yml" }
       - { resource: "@EscapeHitherSearchManagerBundle/Resources/config/config.yml" }
    ```
 If you want a a backend to manage your resource. add in your config file

    ```yaml
   scape_hither_search_manager:
       indexes:
           product:
               entity: OpenMarketPlace\ProductManagerBundle\Entity\Product
               index_name: product
               type: product
               tags:
                   categories:
                       include: ['id','code','name']
    ```

2. Import routing files in `app/config/routing.yml`:

    ```yaml
    escape_hither_security_manager:
        resource: "@EscapeHitherSecurityManagerBundle/Resources/config/routing.yml"
        prefix:   /
    ```

3. Configuration reference:

    ```yaml
    escape_hither_security_manager:
        user_provider:
            class : AppBundle\Entity\User
    ```
Step 5:  Update your database schema
-------------------------
```console
$ bin/console doctrine:schema:update --force
$ bin/console cache:clear -e prod
$ bin/console cache:clear
```
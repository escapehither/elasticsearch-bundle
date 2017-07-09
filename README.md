Escape Hither SecurityManagerBundle
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
$ composer require escapehither/security-manager-bundle dev-master
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

             new EscapeHither\CrudManagerBundle\StarterKitCrudBundle(),
             new EscapeHither\SecurityManagerBundle\StarterKitSecurityManagerBundle(),
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
       - { resource: "@EscapeHitherSecurityManagerBundle/Resources/config/services.yml" }
       - { resource: "@EscapeHitherSecurityManagerBundle/Resources/config/config.yml" }
    ```
 If you want a a backend to manage your resource. add in your config file

    ```yaml
    escape_hither_crud_manager:
        resources:
            user:
                controller: EscapeHither\SecurityManagerBundle\Controller
                entity: AppBundle\Entity\User
                form: EscapeHither\SecurityManagerBundle\Form\UserType
                repository: AppBundle\Repository\UserRepository
    ```

 Import user routing file in `app/config/routing.yml` :
 Change administration to your secure area.
    ```yaml
    escape_hither_manage_user:
            resource: "@EscapeHitherSecurityManagerBundle/Resources/config/routing/user.yml"
            prefix:   /administration/user
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

4. Import security files in `app/config/security.yml`:
    ```yaml
  # To get started with security, check out the documentation:
  # http://symfony.com/doc/current/security.html
  security:

      # http://symfony.com/doc/current/security.html#b-configuring-how-users-are-loaded
      #providers:
          #in_memory:
              #memory: ~
      role_hierarchy:
              ROLE_MANAGER: [ROLE_USER]
              ROLE_ADMIN:       [ROLE_MANAGE_ROOM,ROLE_MANAGE_CUSTOMER,ROLE_MANAGE_RESERVATION]
              ROLE_SUPER_ADMIN: [ROLE_ADMIN,ROLE_MANAGE_USER,ROLE_ALLOWED_TO_SWITCH]
      providers:
              our_users:
                  entity: { class: AppBundle\Entity\User, property: email }
      firewalls:
          # disables authentication for assets and the profiler, adapt it according to your needs
          dev:
              pattern: ^/(_(profiler|wdt)|css|images|js)/
              security: false

          login:
              pattern:  ^/api/login
              stateless: true
              anonymous: true
              form_login:
                  check_path:               /api/login_check
                  success_handler:          lexik_jwt_authentication.handler.authentication_success
                  failure_handler:          lexik_jwt_authentication.handler.authentication_failure
                  require_previous_session: false

          api:
              pattern:   ^/api
              stateless: true
              guard:
                  authenticators:
                      - lexik_jwt_authentication.jwt_token_authenticator
                      #- escapehither.security_jwt_token_authenticator #my authenticator

          main:
              anonymous: ~
              guard:
                  entry_point: escapehither.security_login_form_authenticator
                  authenticators:
                      - escapehither.security_login_form_authenticator
              logout:
                  path: /logout


          secured_area:
              # ...
              form_login:
              # ...
                  #csrf_token_generator: security.csrf.token_manager

      encoders:
              EscapeHither\SecurityManagerBundle\Entity\UserAccountInterface: bcrypt
      access_control:
              - { path: ^/admin, roles: ROLE_MANAGER }
              - { path: ^/profile, roles: ROLE_USER }
              - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
              - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }
    ```


4. Install LexikJWTAuthenticationBundle:
 Add encoder for jwt.

```console
mkdir var/jwt
openssl genrsa -out var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```
Step 5:  Update your database schema
-------------------------
```console
$ bin/console doctrine:schema:update --force
$ bin/console cache:clear -e prod
$ bin/console cache:clear
```
# Puzzle User
**=========================================**

Puzzle bundle for managing user accounts

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

`composer require webundle/puzzle-admin-user`

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

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

            new Puzzle\Admin\UserBundle\PuzzleAdminUserBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3: Register the Routes

Load the bundle's routing definition in the application (usually in the `app/config/routing.yml` file):

# app/config/routing.yml
```yaml
puzzle_admin_user:
        resource: "@PuzzleAdminUserBundle/Resources/config/routing.yml"
```

Step 4: Enable management via Admin modules interface

Then, enable management bundle via admin modules interface by adding it to the list of registered bundles in the `app/config/config.yml` file of your project under:

```yaml
puzzle_admin_user:
    title: user.title
    description: user.description
    icon: user.icon
    roles:
        user:
            label: 'ROLE_USER'
            description: user.role.user
        user_manage:
            label: 'ROLE_USER_MANAGE'
            description: user.role.user_manage
        admin:
            label: 'ROLE_ADMIN'
            description: user.role.admin
        super_admin:
            label: 'ROLE_SUPER_ADMIN'
            description: user.role.super_admin
```


### Step 5: Configure navigation module

Then, enable management bundle via admin modules interface by adding it to the list of registered bundles in the `app/config/config.yml` file of your project under:

```yaml
# Client Admin
puzzle_admin:
    ...
    navigation:
        nodes:
            user:
                label: 'user.base'
                translation_domain: 'admin'
                attr:
                    class: 'icon-user'
                parent: ~
                user_roles: ['ROLE_USER_MANAGE', 'ROLE_ADMIN']
                tooltip: 'user.tooltip'
            user_list:
                label: 'user.base'
                translation_domain: 'admin'
                path: 'admin_user_list'
                sub_paths: ['admin_user_create', 'admin_user_update', 'admin_user_show']
                parent: user
                user_roles: ['ROLE_USER_MANAGE', 'ROLE_ADMIN']
                tooltip: 'user.tooltip'
            user_group:
                label: 'user.group.base'
                translation_domain: 'admin'
                path: 'admin_user_group_list'
                parent: user
                user_roles: ['ROLE_USER_MANAGE', 'ROLE_ADMIN']
                tooltip: 'user.group.tooltip'
```
# Laravel permission, role based rights
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](http://choosealicense.com/licenses/mit/)
## Contents
- [Key features](#key-features)
- [Installation](#installation)
  - [Migration](#migration)
  - [Models](#models)
    - [Role](#role)
    - [Permission](#permission)
    - [User](#user)
- [Concept](#concept)
  - [For example](#for-example)
- [Usage](#usage)
  - [Permission checking](#permission-checking)
    - [Simple checking](#simple-checking)
    - [Middleware](#middleware)
    - [Blade](#blade)
  - [Role checking](#permission-checking)
    - [Simple checking](#simple-checking)
  - [Attaching, detaching](#attaching-detaching)
    - [Attach to a user](#attach-to-a-user)
    - [Detach from a user](#detacg-from-a-user)
    - [Attach to a role](#attach-to-a-role)
    - [Detach from a role](#detach-from-a-role)

## Key features
1. You have roles and permissions. Permissions can be attached to a role, and roles can be attached to a single user, but also you can **attach or detach permission/permissions from a specific user.**
2. Permissions and roles are stored in the **cache**, editing them automatically refreshes it. We use cache tags, so regular file or database cache drivers doesn't work, please use **memcached** instead.
3. **Inspired by [Zizaco/entrust](https://github.com/Zizaco/entrust)**
4. Please note, the package is under development, only tested with **Laravel 5.2**.

## Installation

Add the following line to your **composer.json** (Laravel 5.4 and below):

    "xdroidteam/xtrust": "0.1.*"

Add the following line to your **composer.json** (Laravel 5.5):

    "xdroidteam/xtrust": "0.2.*"

Then run `composer update`.

or you can run the `composer require` command from your terminal:

    composer require xdroidteam/xtrust

Then in your `config/app.php` add to the `providers` array (not needed after Laravel 5.5):
```php
XdroidTeam\XTrust\XTrustServiceProvider::class,
```
and to `aliases` array (not needed after Laravel 5.5):
```php
'XTrust' => XdroidTeam\XTrust\XTrust::class,
```

If you are going to use [Middleware](#middleware) (requires Laravel 5.1 or later) you also need to add
```php
'permission' => \XdroidTeam\XTrust\Middleware\XTrustPermissionMiddleware::class,
```
to `routeMiddleware` array in `app/Http/Kernel.php`.

### Migration

Deploy migration file:

```bash
php artisan vendor:publish --tag=xdroidteam-xtrust
```


You may now run it with the *artisan migrate* command:

```bash
php artisan migrate
```

### Models

#### Role

Create a Role model inside `app/models/Role.php` using the following example:

```php
<?php namespace App\Models;

use XdroidTeam\XTrust\Models\XTrustRole;

class Role extends XTrustRole
{
  ...
}
```


#### Permission

Create a Permission model inside `app/models/Permission.php` using the following example:

```php
<?php namespace App\Models;

use XdroidTeam\XTrust\Models\XTrustPermission;

class Permission extends XTrustPermission
{
  ...
}
```
#### User

Next, use the `XTrustUserTrait` trait in your existing `User` model. For example:

```php
<?php

use XdroidTeam\XTrust\Traits\XTrustUserTrait;

class User extends Eloquent
{
    use XTrustUserTrait; // add this trait to your user model

    ...
}
```

Don't forget to dump composer autoload

```bash
composer dump-autoload
```

## Concept
There are roles and permissions. You can attach many permissions to a role, and attach many roles to a user, like in Zizaco/entrust. The main difference, that you can directly attach or detach permissions to a user.

### For example
You have four permissions:
1. can_show
2. can_create
3. can_edit
4. can_delete

You have two roles, with permissions:
1. admin:
  1. can_show
  2. can_create
  3. can_edit
  4. can_delete
2. user:
  1. can_show

You have two users, with roles:
1. Adam Admin:
  1. admin
2. Super User
  1. user

If you don't want Adam Admin, to be able to delete, you can simply detach the can_delete permission from him. The admin group can still have the can_delete permission, but Adam will not.
If you want Super User to be able to edit, you can attach this permisson (can_edit) to her. The other users in the user role will still be unable to edit, except her.

Because of this logic, **you can't check the user roles, only the permissions!**

Example for UI:
![Screenshot](https://raw.githubusercontent.com/xdroidteam/images/master/xtrusUI.png)

## Usage
### Permission checking
#### Simple checking
Check one permission:
```php
XTrust::hasPermission('can_delete');
```
Returns true, if the authanticated user has the permission, returns false if not.

Check multiple permissions:
```php
XTrust::hasPermissions(['can_delete', 'can_edit']);
```
Returns true, if the authanticated user has all the permissions, returns false if not.

Or:
```php
XTrust::hasOneOfPermissions(['can_delete', 'can_edit']);
```
Returns true, if the authanticated user has one of the permissions, returns false if not.

You can also check within the user model:
```php
$user = User::find(1);
$user->hasPermission('can_delete');
// OR
$user->hasPermissions(['can_delete', 'can_edit']);
// OR
$user->hasOneOfPermissions(['can_delete', 'can_edit']);
```

#### Middleware
```php
Route::group(['middleware' => ['auth', 'permission:can_show']], function(){
    Route::get('/', 'HomeController@index');
});
```
For multiple permission check use pipe symbol as OR operator:
```php
Route::group([
	'middleware' => [
    	'auth',
        'permission:can_show|can_create|can_edit|can_delete'
    ]
], function(){
    Route::get('/admin', 'AdminController@index');
});
```
To emulate AND functionality just use multiple instances of middleware
For multiple permission check use pipe symbol as OR operator:
```php
Route::group([
	'middleware' => [
    	'auth',
        'permission:can_show',
        'permission:can_create'
    ]
], function(){
    Route::get('/admin', 'AdminController@index');
});
```
#### Blade
```php
@permission('can_delete')
	{!! Form::open([
    	'url' => '/users/'.$user->id,
        'method'=> "DELETE"
    ] ) !!}
        <button class="btn btn-sm btn-danger">
            Delete
        </button>
    {!! Form::close() !!}
@endpermission
```
Multiple permissions:
```php
@permissions(['can_show', 'can_delete'])
	<span>Something</span>
@endpermissions
```
Returns true, if the authanticated user has all the permissions, returns false if not.

Or:
```php
@oneofpermissions(['can_show', 'can_delete'])
	<span>Something</span>
@endoneofpermissions
```
Returns true, if the authanticated user has one of the permissions, returns false if not.
### Role checking
#### Simple checking
Check one role:
```php
XTrust::hasRole('can_delete');
```
Returns true, if the authanticated user has the role, returns false if not.

Check multiple roles:
```php
XTrust::hasRoles(['can_delete', 'can_edit']);
```
Returns true, if the authanticated user has all the roles, returns false if not.

Or:
```php
XTrust::hasOneOfRoles(['can_delete', 'can_edit']);
```
Returns true, if the authanticated user has one of the roles, returns false if not.

You can also check within the user model:
```php
$user = User::find(1);
$user->hasRole('can_delete');
// OR
$user->hasRoles(['can_delete', 'can_edit']);
// OR
$user->hasOneOfRoles(['can_delete', 'can_edit']);
```

### Attaching detaching
**Always use the the id of the role or permission for attaching/detaching!**
#### Attach to a user
Attach one role to a user:
```php
$user->attachRole(1);
```
Attach multiple roles to a user:
```php
$user->attachRoles([1,2]);
```
Attach one permission to a user:
```php
$user->attachPermission(1);
```
Attach multiple permissions to a user:
```php
$user->attachPermissions([1,2]);
```
#### Detach from a user
Detach one role from a user:
```php
$user->detachRole(1);
```
Detach multiple roles from a user:
```php
$user->detachRoles([1,2]);
```
Detach one permission from a user:
```php
$user->detachPermission(1);
```
Detach multiple permissions from a user:
```php
$user->detachPermissions([1,2]);
```
#### Attach to a role
Attach one permission to a role:
```php
$role->attachPermission(1);
```
Attach multiple permissions to a role:
```php
$role->attachPermissions([1,2]);
```
#### Detach from a role
Detach one permission from a role:
```php
$role->detachPermission(1);
```
Detach multiple permissions from a role:
```php
$role->detachPermissions([1,2]);
```

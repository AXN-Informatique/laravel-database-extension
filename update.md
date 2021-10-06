Updates
=======

From version 2.x to version 3.x
-------------------------------

### Remove ModelTrait from model classes

In version 2.x, the trait `Axn\Illuminate\Database\Eloquent\ModelTrait` was needed
on model classes to use these features:

* [Joins using relationships](#joins-using-relationships)
* [Default order](#default-order)

In version 3.x, this trait has been removed so you need to remove it from model classes.

For example, replace:

```php
namespace App\Models;

use Axn\Illuminate\Database\Eloquent\ModelTrait;

class User extends Model
{
    use ModelTrait;

    // ...
}
```

By:

```php
namespace App\Models;

class User extends Model
{
    // ...
}
```


#### Joins using relationships

This feature is now implemented using Eloquent macros and no longer require any
additions on model classes to work.


#### Default order

This feature is now implemented using a global scope and need to replace the attribute
`$orderBy` in model classes by the registration of the global scope instead.

For example, replace:

```php
class User extends Model
{
    // ...

    protected $orderBy = [
        'lastname' => 'asc',
        'firstname' => 'asc',
    ];

    // ...
}
```

By:

```php
use Axn\Illuminate\Database\Eloquent\DefaultOrderScope;

class User extends Model
{
    // ...

    protected static function booted()
    {
        static::addGlobalScope(new DefaultOrderScope([
            'lastname'  => 'asc',
            'firstname' => 'desc',
        ]));
    }

    // ...
}
```

And for disabling default order on queries, replace the call of `disableDefaultOrderBy()`
by `withoutGlobalScope(DefaultOrderScope::class)`.

For example, replace:

```php
$users = User::disableDefaultOrderBy()->get();
```

By:

```php
use Axn\Illuminate\Database\Eloquent\DefaultOrderScope;

$users = User::withoutGlobalScope(DefaultOrderScope::class)->get();
```


### Automatically set engine InnoDB with Schema::create()

In version 2.x, when using `Schema::create()`, engine was automatically set to "InnoDB"
on Blueprint instance if not set manually.

In version 3.x, this feature has been removed because this can be done by adding
`'engine' => 'InnoDB'` option to mysql connection in `config/database.php`.

For example:

```php
// config/database.php

return [
    // ...

    'connections' => [
        // ...

        'mysql' => [
            // ...
            'engine' => 'InnoDB',
        ],

        // ...
    ],

    // ...
];
```

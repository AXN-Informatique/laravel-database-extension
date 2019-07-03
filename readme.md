Laravel Database Extension
==========================

Includes some extensions/improvements to the Database section of Laravel Framework

* [Installation](#installation)
* [Usage](#usage)
    - [Natural sort](#natural-sort)
    - [Default model sort](#default-model-sort)
    - [Joins using relationships](#joins-using-relationships)
    - [Query builder whereLike macro](#query-builder-wherelike-macro)
* [Soft deletes](#soft-deletes)

Installation
------------

With Composer:

```sh
composer require axn/laravel-database-extension
```

Usage
-----

Add trait `Axn\Illuminate\Database\Eloquent\ModelTrait` to models:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Axn\Illuminate\Database\Eloquent\ModelTrait;

class User extends Model
{
    use ModelTrait;

    // ...
}
```

### Natural sort

Method `orderByNatural` has been added to QueryBuilder (macro) for naturel sorting
(see: http://kumaresan-drupal.blogspot.fr/2012/09/natural-sorting-in-mysql-or.html).
Use it like `orderBy`.

Exemple:

```php
DB::table('appartements')->orderByNatural('numero')->get();

// Descendant
DB::table('appartements')->orderByNatural('numero', 'desc')->get();
```

### Default model sort

Add the attribute `$orderBy` to the model if you want to have select results
automatically sorted:

```php
protected $orderBy = 'nom_champ';

// OR
protected $orderBy = [
    'nom_champ1' => 'option',
    'nom_champ2' => 'option',
    ...
];
```

`option` can be :

- asc
- desc
- natural
- natural_asc *(same as "natural")*
- natural_desc

Example:

```php
class User extends Model
{
    use ModelTrait;

    protected $orderBy = [
        'lastname'  => 'asc',
        'firstname' => 'desc',
    ];
}
```

If you don't want the default sort applied, simply call `disableDefaultOrderBy()` on the model:

```php
$users = User::disableDefaultOrderBy()->get();
```

Note that the default sort is automatically disabled if you manually set ORDER BY clause.

### Joins using relationships

This is the most important feature of this package: you can do joins using Eloquent relationships!

WARNING: only BelongsTo, HasOne, HasMany, MorphOne and MorphMany relations are supported.
So, if you want to use BelongsToMany, you have to go with the HasMany/BelongsTo relations
to/from the pivot table.

Example:

```php
// instead of doing joinRel('roles') (User belongs-to-many Role)
User::joinRel('userHasRoles') // User has-many UserHasRole
    ->joinRel('userHasRoles.role') // UserHasRole belongs-to Role
    ->get();

// with aliases:
User::alias('u')
    ->joinRel('userHasRoles', 'uhr')
    ->joinRel('uhr.role', 'r')
    ->get();
```

You may also want to use:

- leftJoinRel()
- rightJoinRel()

Or if the model uses SoftDeletes and you want to include trashed records:

- joinRelWithTrashed()
- leftJoinRelWithTrashed()
- rightJoinRelWithTrashed()

And to add additionnal criteria:

```php
User::joinRel('userHasRoles', function($join) {
        $join->where('is_main', 1);
    })
    ->joinRel('userHasRoles.role')
    ->get();
```

Note that additionnal criteria are automatically added if they are defined on the relation:

```php
class User extends Model
{
    use ModelTrait;

    // joinRel('mainAddress', 'a') will do:
    // join `addresses` as `a` on `a`.`user_id` = `users`.`id` and `a`.`is_main` = 1
    public function mainAddress()
    {
        return $this->hasOne('addresses')->where('is_main', 1);
    }
}
```

### Eloquent Query builder whereLike macro

Source : https://murze.be/searching-models-using-a-where-like-query-in-laravel

**Warning!** This only works on instances of the *Eloquent Builder*, not on the generic Query Builder.

A replacement of this:

```php
User::query()
   ->where('name', 'LIKE', "%{$searchTerm}%")
   ->orWhere('email', 'LIKE', "%{$searchTerm}%")
   ->get();
```

By that:

```php
User::whereLike(['name', 'email'], $searchTerm)->get();
```

Or more advanced, a replacement of this:

```php
Post::query()
   ->where('name', 'LIKE', "%{$searchTerm}%")
   ->orWhere('text', 'LIKE', "%{$searchTerm}%")
   ->orWhereHas('author', function ($query) use ($searchTerm) {
        $query->where('name', 'LIKE', "%{$searchTerm}%");
   })
   ->orWhereHas('tags', function ($query) use ($searchTerm) {
        $query->where('name', 'LIKE', "%{$searchTerm}%");
   })
   ->get();
```

By that:

```php
Post::whereLike(['name', 'text', 'author.name', 'tags.name'], $searchTerm)->get();
```

Soft deletes
------------

Our Soft Deletes trait extends the Eloquent one.

This allows us to provide the `scopeWithoutTrashedExcept` method :

```php
$postTypes = PostType::withoutTrashedExcept($post->post_type_id)->get();
```

To use it, add the trait `Axn\Illuminate\Database\Eloquent\SoftDeletes` to models:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Axn\Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    // ...
}
```


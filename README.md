Laravel Database Extension
==========================

Includes some extensions/improvements to the Database section of Laravel Framework

* [Installation](#installation)
* [Usage](#usage)
    - [Natural sorting](#natural-sorting)
    - [Default order](#default-order)
    - [Joins using relationships](#joins-using-relationships)
    - [Eloquent whereHasIn macro](#eloquent-wherehasin-macro)
    - [Eloquent whereLike macro](#eloquent-wherelike-macro)
    - [SoftDeletes withoutTrashedExcept scope](#softdeletes-withouttrashedexcept-scope)


Installation
------------

With Composer:

```sh
composer require axn/laravel-database-extension
```

Usage
-----

### Natural sorting

***Caution!** These methods only work on columns that contain only values ​​that begin with numerics.*

Method `orderByNatural` has been added to QueryBuilder (macro) for natural sorting
(see: http://kumaresan-drupal.blogspot.fr/2012/09/natural-sorting-in-mysql-or.html).
Use it like `orderBy`.

Example:

```php
DB::table('appartements')->orderByNatural('numero')->get();

// Descendant
DB::table('appartements')->orderByNatural('numero', 'desc')->get();
// or
DB::table('appartements')->orderByNaturalDesc('numero')->get();
```

### Default order

Although we no longer recommend using this global scope, we leave it for compatibility reasons regarding older applications.
Indeed, this scope, as it automatically applies an orderBy clause, can cause performance problems on certain queries. For example, if it is applied to a relation that is itself used in a `whereHas()`.

Add the global scope `DefaultOrderScope` to the model if you want to have select results automatically sorted:

```php
use Axn\Illuminate\Database\Eloquent\DefaultOrderScope;

class MyModel extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new DefaultOrderScope([
            'column' => 'option',
        ]));
    }
}
```

`option` can be:

- 'asc'
- 'desc'
- 'natural' *(apply `orderByNatural()`)*
- 'natural_asc' *(same as 'natural')*
- 'natural_desc' *(same as 'natural' but descendant)*
- 'raw' *(apply `orderByRaw()`)*

If you don't precise option, it will be "asc" by default.

Example:

```php
use Axn\Illuminate\Database\Eloquent\DefaultOrderScope;

class User extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new DefaultOrderScope([
            'lastname'  => 'asc',
            'firstname' => 'desc',
        ]));
    }
}
```

If you don't want the default order applied, simply use the Eloquent method
`withoutGlobalScope()` on the model:

```php
$users = User::withoutGlobalScope(DefaultOrderScope::class)->get();
```

Note that the default order is automatically disabled if you manually set `ORDER BY` clause.


### Joins using relationships

This is the most important feature of this package: you can do joins using Eloquent relationships!

**WARNING:** only BelongsTo, HasOne, HasMany, MorphOne and MorphMany relations are supported.
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

And to add extra criteria:

```php
User::joinRel('userHasRoles', function ($join) {
        $join->where('is_main', 1);
    })
    ->joinRel('userHasRoles.role')
    ->get();
```

Note that extra criteria are automatically added if they are defined on the relation:

```php
class User extends Model
{
    // joinRel('mainAddress', 'a') will do:
    // join `addresses` as `a` on `a`.`user_id` = `users`.`id` and `a`.`is_main` = 1
    public function mainAddress()
    {
        return $this->hasOne('addresses')->where('is_main', 1);
    }
}
```

**WARNING:** an instance of JoinRelBuilder is created and attached to the Eloquent Builder instance
via WeakMap to handle this feature. If you ever clone the Builder instance, note that there is
no cloning of the attached JoinRelBuilder instance. This can be a problem if you use "joinRel"
on the cloned instance with a reference to an alias created in the original instance.

For example:

```php
$originalBuilder = User::joinRel('userHasRoles');

$clonedBuilder = clone $originalBuilder;

// Produces error: No model with alias "userHasRoles"
$clonedBuilder->joinRel('userHasRoles.role');
```

If you need to handle this case, use the "cloneWithJoinRelBuilder" method instead of clone:

```php
$originalBuilder = User::joinRel('userHasRoles');

$clonedBuilder = $originalBuilder->cloneWithJoinRelBuilder();

$clonedBuilder->joinRel('userHasRoles.role');
```


### Eloquent whereHasIn macro

If you have performance issues with the `whereHas` method, you can use `whereHasIn` instead.

It uses `in` clause instead of `exists` to check existence:

```php
// where exists (select * from `comments` where `comments`.`post_id` = `posts`.`id`)
Post::whereHas('comments')->get();

// where `posts`.`id` in (select `comments`.`post_id` from `comments`)
Post::whereHasIn('comments')->get();
```

You can use a callback to add extra criteria:

```php
// where `posts`.`id` in (
//     select `comments`.`post_id` from `comments`
//     where `comments`.`content` like "A%"
// )
Post::whereHasIn('comments', function ($query) {
    $query->where('content', 'like', "A%");
})->get();
```

Note that it does not support "dot" notation, but you can use joins:

```php
// where `posts`.`id` in (
//     select `comments`.`post_id` from `comments`
//     inner join `users` as `author` on `author`.`id` = `comments`.`author_id`
//     where `author`.`lastname` like "A%"
// )
Post::whereHasIn('comments', function ($query) {
    $query
        ->joinRel('author')
        ->where('author.lastname', 'like', "A%");
})->get();
```

You may also want to use:

- orWhereHasIn()
- whereDoesntHaveIn()
- orWhereDoesntHaveIn()


### Eloquent whereLike macro

Source: https://murze.be/searching-models-using-a-where-like-query-in-laravel

**Warning!** This only works on instances of the *Eloquent Builder*, not on the generic Query Builder.

A replacement of this:

```php
User::query()
   ->where('name', 'like', "%{$searchTerm}%")
   ->orWhere('email', 'like', "%{$searchTerm}%")
   ->get();
```

By that:

```php
User::whereLike(['name', 'email'], $searchTerm)->get();
```

Or more advanced, a replacement of this:

```php
Post::query()
   ->where('name', 'like', "%{$searchTerm}%")
   ->orWhere('text', 'like', "%{$searchTerm}%")
   ->orWhereHas('author', function ($query) use ($searchTerm) {
        $query->where('name', 'like', "%{$searchTerm}%");
   })
   ->orWhereHas('tags', function ($query) use ($searchTerm) {
        $query->where('name', 'like', "%{$searchTerm}%");
   })
   ->get();
```

By that:

```php
Post::whereLike(['name', 'text', 'author.name', 'tags.name'], $searchTerm)->get();
```


### SoftDeletes withoutTrashedExcept scope

Our `SoftDeletes` trait extends the Eloquent one to provide the `withoutTrashedExcept` scope :

```php
$postTypes = PostType::withoutTrashedExcept($post->post_type_id)->get();

// you also can provide multiple ids:
$postTypes = PostType::withoutTrashedExcept([1, 2, 3])->get();
```

To use it, add the trait `Axn\Illuminate\Database\Eloquent\SoftDeletes` to models:

```php
namespace App\Models;

use Axn\Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use SoftDeletes;

    // ...
}
```

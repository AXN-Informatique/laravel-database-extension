---
title: Joins using relationships
order: 4
---

Joins using relationships
=========================

This is the most important feature of this package: you can perform SQL joins using Eloquent relationships!

Supported relationships
-----------------------

- BelongsTo
- HasOne
- HasMany
- MorphOne
- MorphMany

**Note:** BelongsToMany is not directly supported. To achieve similar results, use HasMany/BelongsTo relations through the pivot table.

Basic usage
-----------

```php
User::joinRel('posts')->get();
```

This generates a join clause based on the `posts` relationship defined on the User model.

Available methods
-----------------

| Method | Description |
|--------|-------------|
| `joinRel()` | Inner join |
| `leftJoinRel()` | Left join |
| `rightJoinRel()` | Right join |
| `joinRelWithTrashed()` | Inner join including soft-deleted records |
| `leftJoinRelWithTrashed()` | Left join including soft-deleted records |
| `rightJoinRelWithTrashed()` | Right join including soft-deleted records |

Using aliases
-------------

You can specify an alias for the joined table:

```php
User::alias('u')
    ->joinRel('posts', 'p')
    ->select('u.*', 'p.title as post_title')
    ->get();
```

Chained relationships
---------------------

Use dot notation to join through multiple relationships:

```php
User::joinRel('posts')
    ->joinRel('posts.comments')
    ->get();
```

With aliases:

```php
User::alias('u')
    ->joinRel('posts', 'p')
    ->joinRel('p.comments', 'c')
    ->get();
```

BelongsToMany workaround
------------------------

Since BelongsToMany is not directly supported, use the pivot table relations:

```php
// Instead of joinRel('roles') (User belongs-to-many Role)
User::joinRel('userHasRoles')      // User has-many UserHasRole
    ->joinRel('userHasRoles.role') // UserHasRole belongs-to Role
    ->get();

// With aliases
User::alias('u')
    ->joinRel('userHasRoles', 'uhr')
    ->joinRel('uhr.role', 'r')
    ->get();
```

Adding extra criteria
---------------------

You can add additional conditions to the join:

```php
User::joinRel('posts', function ($join) {
        $join->where('is_published', true);
    })
    ->get();
```

### Automatic criteria from relationship

Extra criteria defined on the relationship are automatically added to the join:

```php
class User extends Model
{
    public function mainAddress()
    {
        return $this->hasOne(Address::class)->where('is_main', true);
    }
}

// joinRel('mainAddress', 'a') generates:
// JOIN addresses AS a ON a.user_id = users.id AND a.is_main = 1
User::joinRel('mainAddress', 'a')->get();
```

Cloning builders
----------------

An instance of `JoinRelBuilder` is attached to the Eloquent Builder via WeakMap. If you clone the Builder, the JoinRelBuilder is not cloned, which can cause issues:

```php
$originalBuilder = User::joinRel('posts');
$clonedBuilder = clone $originalBuilder;

// Error: No model with alias "posts"
$clonedBuilder->joinRel('posts.comments');
```

Use `cloneWithJoinRelBuilder()` instead:

```php
$originalBuilder = User::joinRel('posts');
$clonedBuilder = $originalBuilder->cloneWithJoinRelBuilder();

// Works correctly
$clonedBuilder->joinRel('posts.comments');
```

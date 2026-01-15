---
title: Default order
order: 3
---

Default order
=============

The `DefaultOrderScope` global scope automatically applies an `ORDER BY` clause to all queries on a model.

**Warning:** We no longer recommend using this global scope for new applications. It is kept for compatibility with older applications. This scope can cause performance issues on certain queries, especially when applied to relations used in `whereHas()` clauses.

Basic usage
-----------

Add the global scope to your model:

```php
use Axn\Illuminate\Database\Eloquent\DefaultOrderScope;

class User extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new DefaultOrderScope([
            'lastname' => 'asc',
            'firstname' => 'asc',
        ]));
    }
}
```

Available options
-----------------

The scope accepts an array where keys are column names and values are sort options:

| Option | Description |
|--------|-------------|
| `asc` | Ascending order (default if no option specified) |
| `desc` | Descending order |
| `natural` | Natural sorting using `orderByNatural()` |
| `natural_asc` | Same as `natural` |
| `natural_desc` | Natural sorting in descending order |
| `raw` | Raw SQL expression using `orderByRaw()` |

Examples
--------

### Simple ascending order

```php
static::addGlobalScope(new DefaultOrderScope([
    'name',  // Equivalent to 'name' => 'asc'
]));
```

### Multiple columns

```php
static::addGlobalScope(new DefaultOrderScope([
    'lastname' => 'asc',
    'firstname' => 'desc',
]));
```

### Natural sorting

```php
static::addGlobalScope(new DefaultOrderScope([
    'number' => 'natural',
]));
```

### Raw SQL expression

```php
static::addGlobalScope(new DefaultOrderScope([
    'FIELD(status, "active", "pending", "closed")' => 'raw',
]));
```

Disabling default order
-----------------------

The default order is automatically disabled if you manually set an `ORDER BY` clause on the query.

You can also explicitly disable it using Laravel's `withoutGlobalScope()` method:

```php
use Axn\Illuminate\Database\Eloquent\DefaultOrderScope;

$users = User::withoutGlobalScope(DefaultOrderScope::class)->get();
```

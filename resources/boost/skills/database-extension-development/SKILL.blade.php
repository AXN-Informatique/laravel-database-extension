---
name: database-extension-development
description: "Activate when working with joinRel, leftJoinRel, rightJoinRel, whereHasIn, whereLike, orderByNatural, DefaultOrderScope, or the extended SoftDeletes trait from axn/laravel-database-extension. Covers join relationships, performance queries, natural sorting, search macros, and soft-delete extensions."
license: MIT
metadata:
  author: axn
---
# Laravel Database Extension — Development Guide

## Documentation

- See `docs/` directory for detailed patterns and documentation per feature.
- See `README.md` for installation and basic usage.

## Join Relations (`joinRel`)

Join tables using Eloquent relationship definitions instead of manual join clauses.

**Supported relationships:** BelongsTo, HasOne, HasMany, MorphOne, MorphMany.

```php
// Inner join
$query->joinRel('author');

// Left join with alias
$query->leftJoinRel('author', 'a');

// Chained relationships via dot notation
$query->joinRel('posts.comments');

// With additional join conditions
$query->joinRel('posts', callback: function ($join) {
    $join->where('posts.published', true);
});

// Include soft-deleted records
$query->joinRelWithTrashed('author');
$query->leftJoinRelWithTrashed('author');
```

**BelongsToMany:** Not directly supported. Define a BelongsTo/HasMany relation on the pivot model and chain joins instead.

## whereHasIn (Performance Alternative)

Uses `WHERE IN` instead of `WHERE EXISTS` — faster in many scenarios.

**Supported relationships:** HasOne, HasMany, BelongsTo, BelongsToMany, HasManyThrough.

```php
// Basic usage
$users = User::whereHasIn('posts')->get();

// With conditions
$users = User::whereHasIn('posts', function ($query) {
    $query->where('published', true);
})->get();

// Negation
$users = User::whereDoesntHaveIn('posts')->get();

// OR variant
$users = User::orWhereHasIn('comments')->get();
```

**Limitation:** Does not support dot notation for nested relationships. Use joins within the callback instead.

## whereLike (Search Macro)

Search across multiple columns and relationships. Works with Eloquent Builder only.

```php
// Search across multiple columns
$users = User::whereLike(['name', 'email'], $searchTerm)->get();

// Search across relationships (dot notation)
$users = User::whereLike(['name', 'posts.title', 'tags.name'], $searchTerm)->get();
```

Spaces in `$searchTerm` are converted to wildcards: `"john doe"` becomes `%john%doe%`.

## Natural Sorting

Sort naturally so that `1, 2, 10` appears instead of `1, 10, 2`. Works on both Query Builder and Eloquent.

```php
$items = Item::orderByNatural('reference')->get();
$items = Item::orderByNaturalDesc('reference')->get();
```

**Caution:** Only works on columns whose values begin with numeric characters.

## DefaultOrderScope

Define default ordering on a model. Auto-disables when a manual ORDER BY is set.

```php
use Axn\Illuminate\Database\Eloquent\DefaultOrderScope;

protected static function booted(): void
{
    static::addGlobalScope(new DefaultOrderScope([
        'name' => 'asc',
        'reference' => 'natural',
    ]));
}
```

**Options:** `asc`, `desc`, `natural`, `natural_asc`, `natural_desc`, `raw`.

**Note:** Kept for backward compatibility. Not recommended for new applications due to potential performance implications.

## Extended SoftDeletes

Drop-in replacement for Laravel's `SoftDeletes` trait, adding `withoutTrashedExcept()`.

```php
use Axn\Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
}

// In a dropdown: show active users + the currently selected (possibly deleted) user
$users = User::withoutTrashedExcept($currentUserId)->get();

// Multiple IDs
$users = User::withoutTrashedExcept([1, 2, 3])->get();
```

## Common Pitfalls

- `orderByNatural` only works on columns starting with numbers — other values sort as empty strings.
- `whereHasIn` does not support dot notation for nested relationships — use callback joins instead.
- `whereLike` only works on Eloquent Builder, not on the base Query Builder.
- `joinRel` does not support BelongsToMany directly — use pivot model relations.
- `DefaultOrderScope` can cause unexpected behavior with pagination or complex queries — prefer explicit ordering.

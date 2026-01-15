---
title: whereHasIn
order: 5
---

whereHasIn
==========

If you have performance issues with the `whereHas()` method, you can use `whereHasIn()` instead.

It uses an `IN` clause instead of `EXISTS` to check for related records, which can be significantly faster in some cases.

Basic usage
-----------

### Standard whereHas (uses EXISTS)

```php
// WHERE EXISTS (SELECT * FROM comments WHERE comments.post_id = posts.id)
Post::whereHas('comments')->get();
```

### whereHasIn (uses IN)

```php
// WHERE posts.id IN (SELECT comments.post_id FROM comments)
Post::whereHasIn('comments')->get();
```

Available methods
-----------------

| Method | Description |
|--------|-------------|
| `whereHasIn()` | Filter records that have related records |
| `orWhereHasIn()` | OR variant |
| `whereDoesntHaveIn()` | Filter records that don't have related records |
| `orWhereDoesntHaveIn()` | OR variant |

Adding criteria
---------------

You can pass a callback to add conditions to the subquery:

```php
// WHERE posts.id IN (
//     SELECT comments.post_id FROM comments
//     WHERE comments.content LIKE 'A%'
// )
Post::whereHasIn('comments', function ($query) {
    $query->where('content', 'like', 'A%');
})->get();
```

Supported relationships
-----------------------

- HasOne
- HasMany
- BelongsTo
- BelongsToMany
- HasManyThrough

Using with joins
----------------

The `whereHasIn` method does not support dot notation for nested relationships. However, you can use joins within the callback:

```php
// WHERE posts.id IN (
//     SELECT comments.post_id FROM comments
//     INNER JOIN users AS author ON author.id = comments.author_id
//     WHERE author.lastname LIKE 'A%'
// )
Post::whereHasIn('comments', function ($query) {
    $query
        ->joinRel('author')
        ->where('author.lastname', 'like', 'A%');
})->get();
```

When to use
-----------

The `IN` clause can be faster than `EXISTS` when:

- The related table has many rows
- The subquery returns a small result set
- The database can use an index on the foreign key column

However, `EXISTS` can be faster when:

- The subquery has complex conditions
- The related table has few rows
- The database optimizer can short-circuit with EXISTS

Test both approaches with your actual data to determine which performs better for your use case.

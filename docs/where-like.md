---
title: whereLike
order: 6
---

whereLike
=========

The `whereLike` macro provides a simplified way to search across multiple columns and relationships using `LIKE` queries.

**Note:** This macro only works on the Eloquent Builder, not on the generic Query Builder.

Source: [Searching models using a where like query in Laravel](https://murze.be/searching-models-using-a-where-like-query-in-laravel)

Basic usage
-----------

### Before (standard Laravel)

```php
User::query()
    ->where('name', 'like', "%{$searchTerm}%")
    ->orWhere('email', 'like', "%{$searchTerm}%")
    ->get();
```

### After (with whereLike)

```php
User::whereLike(['name', 'email'], $searchTerm)->get();
```

Searching in relationships
--------------------------

You can search in related models using dot notation:

### Before (standard Laravel)

```php
Post::query()
    ->where('title', 'like', "%{$searchTerm}%")
    ->orWhere('content', 'like', "%{$searchTerm}%")
    ->orWhereHas('author', function ($query) use ($searchTerm) {
        $query->where('name', 'like', "%{$searchTerm}%");
    })
    ->orWhereHas('tags', function ($query) use ($searchTerm) {
        $query->where('name', 'like', "%{$searchTerm}%");
    })
    ->get();
```

### After (with whereLike)

```php
Post::whereLike(['title', 'content', 'author.name', 'tags.name'], $searchTerm)->get();
```

How it works
------------

The macro:

1. Wraps the search in a `where()` closure to group conditions
2. Replaces spaces in the search term with `%` wildcards
3. For direct columns, uses `orWhere()` with `LIKE`
4. For relationship columns (containing a dot), uses `orWhereHas()` with `LIKE`

Space handling
--------------

Spaces in the search term are automatically converted to `%` wildcards:

```php
// Search term: "john doe"
// Becomes: "%john%doe%"
User::whereLike(['name'], 'john doe')->get();
```

This allows matching "John Doe", "Johnny Doering", etc.

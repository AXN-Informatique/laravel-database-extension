---
title: Natural sorting
order: 2
---

Natural sorting
===============

The `orderByNatural` method has been added to QueryBuilder (as a macro) for natural sorting.

This allows sorting alphanumeric values in a human-friendly order: `1, 2, 10` instead of `1, 10, 2`.

**Caution!** These methods only work on columns that contain values beginning with numerics.

Basic usage
-----------

```php
DB::table('apartments')->orderByNatural('number')->get();
```

Descending order
----------------

```php
// Using direction parameter
DB::table('apartments')->orderByNatural('number', 'desc')->get();

// Or using dedicated method
DB::table('apartments')->orderByNaturalDesc('number')->get();
```

How it works
------------

The natural sorting is implemented using MySQL's ability to cast strings to numbers. The generated SQL orders by:

1. Whether the value starts with a number (numeric values first)
2. The numeric value itself
3. The string length
4. The string value alphabetically

This approach is based on [this MySQL natural sorting technique](http://kumaresan-drupal.blogspot.fr/2012/09/natural-sorting-in-mysql-or.html).

Use with Eloquent
-----------------

Since this is a QueryBuilder macro, it works with both the Query Builder and Eloquent:

```php
// Query Builder
DB::table('apartments')->orderByNatural('number')->get();

// Eloquent
Apartment::orderByNatural('number')->get();
```

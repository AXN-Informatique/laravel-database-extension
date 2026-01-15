---
title: Laravel Database Extension
order: 0
---

Laravel Database Extension
==========================

Extensions and improvements to the Database section of Laravel Framework.

Features
--------

- **Natural sorting**: Sort results naturally (1, 2, 10 instead of 1, 10, 2)
- **Default order**: Automatically apply sorting on model queries
- **Joins using relationships**: Perform SQL joins using Eloquent relationships
- **whereHasIn**: Performant alternative to `whereHas()` using `IN` instead of `EXISTS`
- **whereLike**: Simplified search across multiple columns and relationships
- **Extended SoftDeletes**: `withoutTrashedExcept()` scope to exclude trashed records except specific ones

Requirements
------------

- PHP 8.4+
- Laravel 12+

Quick install
-------------

```bash
composer require axn/laravel-database-extension
```

The service provider is automatically discovered by Laravel.

Table of contents
-----------------

- [Installation](./installation.md)
- [Natural sorting](./natural-sorting.md)
- [Default order](./default-order.md)
- [Joins using relationships](./join-relations.md)
- [whereHasIn](./where-has-in.md)
- [whereLike](./where-like.md)
- [Extended SoftDeletes](./soft-deletes.md)

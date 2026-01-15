Laravel Database Extension
==========================

Extensions and improvements to the Database section of Laravel Framework.

Features
--------

- **[Natural sorting](docs/natural-sorting.md)**: Sort results naturally (1, 2, 10 instead of 1, 10, 2)
- **[Default order](docs/default-order.md)**: Automatically apply sorting on model queries
- **[Joins using relationships](docs/join-relations.md)**: Perform SQL joins using Eloquent relationships
- **[whereHasIn](docs/where-has-in.md)**: Performant alternative to `whereHas()` using `IN` instead of `EXISTS`
- **[whereLike](docs/where-like.md)**: Simplified search across multiple columns and relationships
- **[Extended SoftDeletes](docs/soft-deletes.md)**: `withoutTrashedExcept()` scope

Requirements
------------

- PHP 8.4+
- Laravel 12+

Installation
------------

```bash
composer require axn/laravel-database-extension
```

The service provider is automatically discovered by Laravel.

Documentation
-------------

Full documentation is available in the [docs](docs/_index.md) folder.

Upgrade guide
-------------

See [UPGRADE.md](UPGRADE.md) for instructions on upgrading between major versions.

License
-------

This package is open-sourced software licensed under the [MIT license](LICENSE).

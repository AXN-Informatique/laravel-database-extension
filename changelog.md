Changelog
=========

2.3.0 (2019-12-29)
------------------

- Add support for Laravel 6

2.2.3 (2019-10-29)
------------------

- $exceptId could be null in SoftDeletes::scopeWithoutTrashedExcept() method

2.2.2 (2019-07-03)
------------------

- Rename query-builder-macros.php into macros.php
- Rename EloquentQueryBuilder into EloquentBuilder alias

2.2.1 (2019-06-30)
------------------

- Fix and document "whereLike" macro wich only work on Eloquent Query Builder

2.2.0 (2019-04-30)
------------------

- Add the SoftDeletes trait

2.1.2 (2019-03-20)
------------------

- Fix Eloquent\Builder::alias() method

2.1.1 (2019-03-20)
------------------

- Moving the "whereLike" macro to the right place
- Register macro in the boot() method instead of the register() one

2.1.0 (2019-03-20)
------------------

- Add an Eloquent "whereLike" query builder macro

2.0.0 (2019-03-11)
------------------

- Drop support for Laravel < 5.8
- Replace getForeignKey method call by getForeignKeyName

1.2.0 (2019-03-07)
------------------

- Add support for Laravel 5.8

1.1.0 (2018-09-11)
------------------

- add support for Laravel 5.7

1.0.0 (2018-07-20)
------------------

- First release.

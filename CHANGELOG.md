Changelog
=========

4.0.2 (2024-04-04)
------------------

- Fix exception always thrown on DefaultOrderScope
- Fix parameters type hinting on joinRel macro

4.0.1 (2024-04-01)
------------------

- Fix versions in changelog bellow

4.0.0 (2024-04-01)
------------------

- Now requires Laravel 10 or greater
- Now requires PHP 8.2 or greater

3.3.0 (2024-02-21)
------------------

- Use WeakMap instead of dynamic property to store JoinRelBuilder instance and avoid deprecation notice
- Add cloneWithJoinRelBuilder macro

3.2.0 (2023-02-20)
------------------

- Add support for Laravel 10

3.1.1 (2022-06-22)
------------------

- Fix joinRel and wherehasIn when key is not primary key

3.1.0 (2022-02-11)
------------------

- Add support for Laravel 9

3.0.0 (2021-10-06)
------------------

- Remove support of Laravel 7 and earlier
- Remove support of PHP 7 and earlier
- Remove ModelTrait trait and Eloquent Builder extension
- Remove MySqlConnection extension
- Add DefaultOrderScope global scope
- Add whereHasIn macro
- Add orderByNaturalDesc macro

2.6.0 (2020-09-25)
------------------

- Add support for Laravel 8

2.5.0 (2020-03-04)
------------------

- Add support for Laravel 7

2.4.0 (2020-01-31)
------------------

- Support array for except id in softDelete trait
- Replace getKeyName() by getQualifiedKeyName() in softDelete trait

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

<?php

namespace Axn\Illuminate\Database\Eloquent\Mixins;

use Axn\Illuminate\Database\Eloquent\JoinRelBuilder;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use WeakMap;

/** @mixin Builder */
class JoinRelMixin
{
    /**
     * Replace table name by an alias.
     *
     * @param  string  $alias
     * @return Builder
     */
    public function alias()
    {
        return function (string $alias): Builder {
            $this->model->setTable($alias);

            return $this->from((new $this->model())->getTable().' as '.$alias);
        };
    }

    /**
     * Make join using Eloquent relationship.
     *
     * @param  string  $relationName
     * @param  string|Closure|null  $alias
     * @param  Closure|null  $callback
     * @param  string  $type
     * @param  bool  $withTrashed
     * @return Builder
     */
    public function joinRel()
    {
        return function (string $relationName, $alias = null, ?Closure $callback = null, string $type = 'inner', bool $withTrashed = false): Builder {
            global $_joinRelBuildersWeakMap;

            if (! isset($_joinRelBuildersWeakMap)) {
                $_joinRelBuildersWeakMap = new WeakMap();
            }

            if (! isset($_joinRelBuildersWeakMap[$this])) {
                $_joinRelBuildersWeakMap[$this] = new JoinRelBuilder($this->model);
            }

            $_joinRelBuildersWeakMap[$this]->apply($this, $relationName, $alias, $callback, $type, $withTrashed);

            return $this;
        };
    }

    /**
     * Make join using Eloquent relationship and including trashed records.
     *
     * @param  string  $relationName
     * @param  string|Closure|null  $alias
     * @param  Closure|null  $callback
     * @return Builder
     */
    public function joinRelWithTrashed()
    {
        return fn (string $relationName, $alias = null, ?Closure $callback = null) => $this->joinRel($relationName, $alias, $callback, 'inner', true);
    }

    /**
     * Make left join using Eloquent relationship.
     *
     * @param  string  $relationName
     * @param  string|Closure|null  $alias
     * @param  Closure|null  $callback
     * @return Builder
     */
    public function leftJoinRel()
    {
        return fn (string $relationName, $alias = null, ?Closure $callback = null) => $this->joinRel($relationName, $alias, $callback, 'left');
    }

    /**
     * Make left join using Eloquent relationship and including trashed records.
     *
     * @param  string  $relationName
     * @param  string|Closure|null  $alias
     * @param  Closure|null  $callback
     * @return Builder
     */
    public function leftJoinRelWithTrashed()
    {
        return fn (string $relationName, $alias = null, ?Closure $callback = null) => $this->joinRel($relationName, $alias, $callback, 'left', true);
    }

    /**
     * Make right join using Eloquent relationship.
     *
     * @param  string  $relationName
     * @param  string|Closure|null  $alias
     * @param  Closure|null  $callback
     * @return Builder
     */
    public function rightJoinRel()
    {
        return fn (string $relationName, $alias = null, ?Closure $callback = null) => $this->joinRel($relationName, $alias, $callback, 'right');
    }

    /**
     * Make right join using Eloquent relationship and including trashed records.
     *
     * @param  string  $relationName
     * @param  string|Closure|null  $alias
     * @param  Closure|null  $callback
     * @return Builder
     */
    public function rightJoinRelWithTrashed()
    {
        return fn (string $relationName, $alias = null, ?Closure $callback = null) => $this->joinRel($relationName, $alias, $callback, 'right', true);
    }

    /**
     * Clone builder with corresponding JoinRelBuilder instance (in WeakMap).
     *
     * @return Builder
     */
    public function cloneWithJoinRelBuilder()
    {
        return function () {
            global $_joinRelBuildersWeakMap;

            $clone = clone $this;

            $_joinRelBuildersWeakMap[$clone] = clone $_joinRelBuildersWeakMap[$this];

            return $clone;
        };
    }
}

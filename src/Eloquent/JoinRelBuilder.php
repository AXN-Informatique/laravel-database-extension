<?php

namespace Axn\Illuminate\Database\Eloquent;

use Axn\Illuminate\Database\Eloquent\Exceptions\JoinRelException;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;

class JoinRelBuilder
{
    /**
     * Main model instance + related models instances
     *
     * @var array[Model]
     */
    protected $models = [];

    /**
     * Constructor.
     */
    public function __construct(Model $model)
    {
        $this->models[$model->getTable()] = $model;
    }

    /**
     * Apply a join clause on a query using an Eloquent relationship.
     *
     * @param  string  $relationName
     * @param  string|null  $alias
     * @param  Closure|null  $callback
     * @param  string  $type
     * @param  bool  $withTrashed
     * @return void
     */
    public function apply(Builder $query, $relationName, $alias = null, $callback = null, $type = 'inner', $withTrashed = false)
    {
        if (str_contains($relationName, '.')) {
            [$parentAlias, $relationName] = explode('.', $relationName);
        } else {
            $parentAlias = $query->getModel()->getTable();
        }

        if (! isset($this->models[$parentAlias])) {
            throw new JoinRelException('No model with alias "'.$parentAlias.'".');
        }

        if ($alias instanceof Closure) {
            $callback = $alias;
            $alias = null;
        }

        if (! $alias) {
            $alias = $relationName;
        }

        if (isset($this->models[$alias])) {
            throw new JoinRelException('Alias "'.$alias.'" already used.');
        }

        $relation = Relation::noConstraints(fn () => $this->models[$parentAlias]->{$relationName}());

        $this->models[$alias] = $relation->getRelated();

        $table = $relation->getRelated()->getTable().' as '.$alias;

        $relation->getRelated()->setTable($alias);
        $relation->getParent()->setTable($parentAlias);

        $condition = function ($join) use ($relation, $callback, $withTrashed) {
            $this->addCondition($join, $relation, $callback, $withTrashed);
        };

        $query->join($table, $condition, null, null, $type);
    }

    /**
     * Add condition to join clause using Eloquent relationship.
     *
     * Supports: HasOne, HasMany, MorphOne, MorphMany, BelongsTo
     *
     * @param  Closure|null  $callback
     * @param  bool  $withTrashed
     * @return Closure
     */
    protected function addCondition(JoinClause $join, Relation $relation, $callback, $withTrashed)
    {
        if ($relation instanceof HasOneOrMany) {
            $relationKey1 = $relation->getParent()->getTable().'.'.$relation->getLocalKeyName();
            $relationKey2 = $relation->getRelated()->getTable().'.'.$relation->getForeignKeyName();

        } elseif ($relation instanceof BelongsTo) {
            $relationKey1 = $relation->getRelated()->getTable().'.'.$relation->getOwnerKeyName();
            $relationKey2 = $relation->getParent()->getTable().'.'.$relation->getForeignKeyName();

        } else {
            throw new JoinRelException('Relation '.$relation::class.' not supported.');
        }

        $join->on($relationKey1, '=', $relationKey2);

        if ($relation instanceof MorphOneOrMany) {
            $morphType = $relation->getRelated()->getTable().'.'.$relation->getMorphType();
            $join->where($morphType, '=', $relation->getMorphClass());
        }

        if (! $withTrashed && method_exists($relation->getRelated(), 'getQualifiedDeletedAtColumn')) {
            $join->whereNull($relation->getRelated()->getQualifiedDeletedAtColumn());
        }

        $this->addExtraCriteria(
            $join,
            $relation->getBaseQuery()->wheres,
            $relation->getRelated()->getTable()
        );

        if ($callback instanceof Closure) {
            $callback($join);
        }
    }

    /**
     * Adds extra "where" criteria to join clause.
     *
     * @param  string  $alias
     * @return void
     */
    protected function addExtraCriteria(JoinClause $join, array $wheres, $alias)
    {
        foreach ($wheres as $where) {
            if ($where['type'] === 'Nested') {
                $join->where(function ($join) use ($where, $alias) {
                    $this->addExtraCriteria($join, $where['query']->wheres, $alias);
                });
            } else {
                $join->where(
                    $alias.'.'.$where['column'],
                    $where['operator'],
                    $where['value'],
                    $where['boolean']
                );
            }
        }
    }
}

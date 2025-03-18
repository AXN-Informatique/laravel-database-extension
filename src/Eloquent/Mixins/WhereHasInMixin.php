<?php

namespace Axn\Illuminate\Database\Eloquent\Mixins;

use Axn\Illuminate\Database\Eloquent\Exceptions\WhereHasInException;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin Builder
 */
class WhereHasInMixin
{
    /**
     * Like "whereHas()" but using "in" clause instead of "exists".
     *
     * @param  string  $relationName
     * @param  Closure|null  $callback
     * @param  string  $boolean
     * @param  bool  $not
     * @return Builder
     */
    public function whereHasIn()
    {
        return function (string $relationName, ?Closure $callback = null, string $boolean = 'and', bool $not = false): Builder {

            $relation = Relation::noConstraints(fn () => $this->model->{$relationName}());

            $relationSubQuery = $relation->getQuery();

            if ($callback instanceof Closure) {
                $callback($relationSubQuery);
            }

            if ($relation instanceof HasOneOrMany) {
                $relationKey1 = $relation->getParent()->getTable().'.'.$relation->getLocalKeyName();
                $relationKey2 = $relation->getRelated()->getTable().'.'.$relation->getForeignKeyName();

            } elseif ($relation instanceof BelongsTo) {
                $relationKey1 = $relation->getRelated()->getTable().'.'.$relation->getOwnerKeyName();
                $relationKey2 = $relation->getParent()->getTable().'.'.$relation->getForeignKeyName();

            } elseif ($relation instanceof BelongsToMany) {
                $relationKey1 = $relation->getQualifiedParentKeyName();
                $relationKey2 = $relation->getQualifiedForeignPivotKeyName();

            } elseif ($relation instanceof HasManyThrough) {
                $relationKey1 = $relation->getQualifiedLocalKeyName();
                $relationKey2 = $relation->getQualifiedFirstKeyName();

            } else {
                throw new WhereHasInException('Relation '.$relation::class.' not supported.');
            }

            return $this->whereIn(
                $relationKey1,
                $relationSubQuery->select($relationKey2),
                $boolean,
                $not
            );
        };
    }

    /**
     * Like "orWhereHas()" but using "in" clause instead of "exists".
     *
     * @param  string  $relationName
     * @param  Closure|null  $callback
     * @return Builder
     */
    public function orWhereHasIn()
    {
        return fn (string $relationName, ?Closure $callback = null) => $this->whereHasIn($relationName, $callback, 'or');
    }

    /**
     * Like "whereDoesntHave()" but using "in" clause instead of "exists".
     *
     * @param  string  $relationName
     * @param  Closure|null  $callback
     * @return Builder
     */
    public function whereDoesntHaveIn()
    {
        return fn (string $relationName, ?Closure $callback = null) => $this->whereHasIn($relationName, $callback, 'and', true);
    }

    /**
     * Like "orWhereDoesntHave()" but using "in" clause instead of "exists".
     *
     * @param  string  $relationName
     * @param  Closure|null  $callback
     * @return Builder
     */
    public function orWhereDoesntHaveIn()
    {
        return fn (string $relationName, ?Closure $callback = null) => $this->whereHasIn($relationName, $callback, 'or', true);
    }
}

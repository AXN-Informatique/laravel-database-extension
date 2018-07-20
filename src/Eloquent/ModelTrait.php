<?php

namespace Axn\Illuminate\Database\Eloquent;

trait ModelTrait
{
    /**
     * Returns a new instance of the extended Eloquent Builder.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return \Axn\Illuminate\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Returns the value of $orderBy attribute if defined.
     *
     * @return string|array|null
     */
    public function getOrderBy()
    {
        return isset($this->orderBy) ? $this->orderBy : null;
    }
}

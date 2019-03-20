<?php

namespace Axn\Illuminate\Database\Eloquent;

trait ModelTrait
{
    /**
     * Original table when table is replaced by an alias.
     *
     * @var string|null
     */
    private $originalTable = null;

    /**
     * Get the original table associated with the model.
     *
     * @return string
     */
    public function getOriginalTable()
    {
        return $this->originalTable ?: $this->getTable();
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @param  string|null  $alias
     * @return static
     */
    public function newInstance($attributes = [], $exists = false, $alias = null)
    {
        $instance = parent::newInstance($attributes, $exists);
        
        $instance->originalTable = $this->getOriginalTable();
        
        return $instance->setTable($alias ?: $instance->originalTable);
    }
    
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
        return $this->orderBy ?? null;
    }
}

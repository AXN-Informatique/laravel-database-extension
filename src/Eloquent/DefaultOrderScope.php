<?php

namespace Axn\Illuminate\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DefaultOrderScope implements Scope
{
    /**
     * @var array
     */
    protected $orders;

    /**
     * Constructor.
     *
     * @param array $orders
     */
    public function __construct(array $orders)
    {
        $this->orders = $orders;
    }

    /**
     * Apply default "order by" clauses on query.
     *
     * @param  Builder $builder
     * @param  Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($builder->getQuery()->orders) {
            return;
        }

        foreach ($this->orders as $column => $option) {
            if (is_int($column)) {
                $builder->orderByRaw($option);

            } elseif ($option == 'natural_asc') {
                $builder->orderByNatural($model->getTable().'.'.$column, 'asc');

            } elseif ($option == 'natural_desc') {
                $builder->orderByNatural($model->getTable().'.'.$column, 'desc');
                
            } else {
                $builder->orderBy($model->getTable().'.'.$column, $option);
            }
        }
    }
}

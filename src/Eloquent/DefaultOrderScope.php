<?php

namespace Axn\Illuminate\Database\Eloquent;

use Axn\Illuminate\Database\Eloquent\Exceptions\DefaultOrderException;
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
     */
    public function __construct(array $orders)
    {
        $this->orders = $orders;
    }

    /**
     * Apply default "order by" clauses on query.
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($builder->getQuery()->orders) {
            return;
        }

        foreach ($this->orders as $column => $option) {
            if (\is_int($column)) {
                $builder->orderBy($model->getTable().'.'.$option);

            } elseif ($option === 'asc' || $option === 'desc') {
                $builder->orderBy($model->getTable().'.'.$column, $option);

            } elseif ($option === 'natural' || $option === 'natural_asc') {
                $builder->orderByNatural($model->getTable().'.'.$column);

            } elseif ($option === 'natural_desc') {
                $builder->orderByNatural($model->getTable().'.'.$column, 'desc');

            } elseif ($option === 'raw') {
                $builder->orderByRaw($column);

            } else {
                throw new DefaultOrderException('Option "'.$option.'" not supported.');
            }
        }
    }
}

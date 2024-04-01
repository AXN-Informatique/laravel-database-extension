<?php

use Axn\Illuminate\Database\Eloquent\Mixins\JoinRelMixin;
use Axn\Illuminate\Database\Eloquent\Mixins\WhereHasInMixin;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Natural sorting.
 *
 * @see http://kumaresan-drupal.blogspot.fr/2012/09/natural-sorting-in-mysql-or.html
 *
 * @param  string  $column
 * @param  string  $direction
 * @return \Illuminate\Database\Query\Builder
 */
QueryBuilder::macro(
    'orderByNatural',
    function ($column, $direction = 'asc') {
        $column = $this->grammar->wrap($column);
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        return $this->orderByRaw(
            "$column + 0 <> 0 ".($direction === 'asc' ? 'desc' : 'asc').', '
            ."$column + 0 $direction, "
            ."length($column) $direction, "
            ."$column $direction"
        );
    }
);

/**
 * Natural sorting, descendant.
 *
 * @param  string  $column
 * @return \Illuminate\Database\Query\Builder
 */
QueryBuilder::macro(
    'orderByNaturalDesc',
    fn ($column) => $this->orderByNatural($column, 'desc')
);

/**
 * Searching models using a where like query.
 *
 * @see https://freek.dev/1182-searching-models-using-a-where-like-query-in-laravel
 *
 * @param  string|array  $attributes
 * @param  string  $searchTerm
 * @return \Illuminate\Database\Query\Builder
 */
EloquentBuilder::macro(
    'whereLike',
    function ($attributes, $searchTerm) {
        $searchTerm = str_replace(' ', '%', $searchTerm);

        $this->where(function (EloquentBuilder $query) use ($attributes, $searchTerm) {
            foreach (Arr::wrap($attributes) as $attribute) {
                if (Str::contains($attribute, '.')) {
                    [$relationName, $relationAttribute] = explode('.', $attribute);

                    $query->orWhereHas($relationName, function (EloquentBuilder $query) use ($relationAttribute, $searchTerm) {
                        $query->where($relationAttribute, 'like', "%{$searchTerm}%");
                    });
                } else {
                    $query->orWhere($attribute, 'like', "%{$searchTerm}%");
                }
            }
        });

        return $this;
    }
);

// Registering macros using mixin classes
// https://liamhammett.com/laravel-mixins-KEzjmLrx

EloquentBuilder::mixin(new JoinRelMixin());

EloquentBuilder::mixin(new WhereHasInMixin());

<?php

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Natural sorting.
 *
 * @see http://kumaresan-drupal.blogspot.fr/2012/09/natural-sorting-in-mysql-or.html
 *
 * @param  string $column
 * @param  string $direction
 * @return \Illuminate\Database\Query\Builder
 */
QueryBuilder::macro(
    'orderByNatural',
    function($column, $direction = 'asc') {
        $column    = $this->grammar->wrap($column);
        $direction = strtolower($direction) == 'asc' ? 'asc' : 'desc';

        return $this->orderByRaw(
              "$column + 0 <> 0 ".($direction == 'asc' ? 'desc' : 'asc').", "
            . "$column + 0 $direction, "
            . "length($column) $direction, "
            . "$column $direction"
        );
    }
);

// Add an Eloquent "whereLike" query builder macro
EloquentBuilder::macro(
    'whereLike',
    function ($attributes, $searchTerm) {
        $searchTerm = str_replace(' ', '%', $searchTerm);

        $this->where(function (EloquentBuilder $query) use ($attributes, $searchTerm) {
            foreach (Arr::wrap($attributes) as $attribute) {
                $query->when(Str::contains($attribute, '.'),
                    function (EloquentBuilder $query) use ($attribute, $searchTerm) {
                        list($relationName, $relationAttribute) = explode('.', $attribute);

                        $query->orWhereHas($relationName, function (EloquentBuilder $query) use ($relationAttribute, $searchTerm) {
                            $query->where($relationAttribute, 'like', "%{$searchTerm}%");
                        });
                    },
                    function (EloquentBuilder $query) use ($attribute, $searchTerm) {
                        $query->orWhere($attribute, 'like', "%{$searchTerm}%");
                    }
                );
            }
        });

        return $this;
    }
);

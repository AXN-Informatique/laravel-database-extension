<?php

use Illuminate\Database\Query\Builder as QueryBuilder;

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
            . "$column $direction"
        );
    }
);

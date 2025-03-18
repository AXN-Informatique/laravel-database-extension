<?php

namespace Axn\Illuminate\Database;

use Axn\Illuminate\Database\Eloquent\Mixins\JoinRelMixin;
use Axn\Illuminate\Database\Eloquent\Mixins\WhereHasInMixin;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        $this->extendsQueryBuilder();
        $this->extendsEloquentBuilder();
    }

    private function extendsQueryBuilder(): void
    {
        /**
         * Natural sorting.
         *
         * @see http://kumaresan-drupal.blogspot.fr/2012/09/natural-sorting-in-mysql-or.html
         *
         * @param  string  $column
         * @param  string  $direction
         * @return QueryBuilder
         */
        QueryBuilder::macro('orderByNatural',
            function ($column, $direction = 'asc') {
                $column = $this->grammar->wrap($column);
                $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

                return $this->orderByRaw(
                    $column.' + 0 <> 0 '.($direction === 'asc' ? 'desc' : 'asc').', '
                    .\sprintf('%s + 0 %s, ', $column, $direction)
                    .\sprintf('length(%s) %s, ', $column, $direction)
                    .\sprintf('%s %s', $column, $direction)
                );
            }
        );

        /**
         * Natural sorting, descendant.
         *
         * @param  string  $column
         * @return QueryBuilder
         */
        QueryBuilder::macro('orderByNaturalDesc',
            fn ($column) => $this->orderByNatural($column, 'desc')
        );
    }

    private function extendsEloquentBuilder(): void
    {
        /**
         * Searching models using a where like query.
         *
         * @see https://freek.dev/1182-searching-models-using-a-where-like-query-in-laravel
         *
         * @param  string|array  $attributes
         * @param  string  $searchTerm
         * @return EloquentBuilder
         */
        EloquentBuilder::macro('whereLike',
            function ($attributes, $searchTerm) {
                $searchTerm = str_replace(' ', '%', $searchTerm);

                $this->where(function (EloquentBuilder $query) use ($attributes, $searchTerm): void {
                    foreach (Arr::wrap($attributes) as $attribute) {
                        if (Str::contains($attribute, '.')) {
                            [$relationName, $relationAttribute] = explode('.', $attribute);

                            $query->orWhereHas($relationName, function (EloquentBuilder $query) use ($relationAttribute, $searchTerm): void {
                                $query->where($relationAttribute, 'like', '%'.$searchTerm.'%');
                            });
                        } else {
                            $query->orWhere($attribute, 'like', '%'.$searchTerm.'%');
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
    }
}

<?php

namespace Axn\Illuminate\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->replaceMySqlConnection();

        require __DIR__.'/query-builder-macros.php';
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Add an Eloquent "whereLike" query builder macro
        Builder::macro('whereLike', function ($attributes, $searchTerm) {
            $searchTerm = str_replace(' ', '%', $searchTerm);
            $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                foreach (array_wrap($attributes) as $attribute) {
                    $query->when(Str::contains($attribute, '.'),
                        function (Builder $query) use ($attribute, $searchTerm) {
                            list($relationName, $relationAttribute) = explode('.', $attribute);

                            $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
                                $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                            });
                        },
                        function (Builder $query) use ($attribute, $searchTerm) {
                            $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                        }
                    );
                }
            });

            return $this;
        });

    }

    /**
     * Replace MySqlConnection in the IoC by the extended one.
     *
     * @return void
     */
    protected function replaceMySqlConnection()
    {
        Connection::resolverFor(
            'mysql',
            function($connection, $database, $prefix, $config) {
                return new MySqlConnection($connection, $database, $prefix, $config);
            }
        );
    }
}

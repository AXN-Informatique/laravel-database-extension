<?php

namespace Axn\Illuminate\Database;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Database\Connection;

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

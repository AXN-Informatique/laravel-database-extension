<?php

namespace Axn\Illuminate\Database;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->replaceMySqlConnection();
    }

    public function boot()
    {
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

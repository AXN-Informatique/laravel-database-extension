<?php

namespace Axn\Illuminate\Database;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        require __DIR__.'/macros.php';
    }
}

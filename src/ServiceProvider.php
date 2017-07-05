<?php

namespace Jano\Cacheable;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider as ParentServiceProvider;
use Jano\Cacheable\Cache\SecureFileStore;

class ServiceProvider extends ParentServiceProvider
{
    public function boot()
    {
        Cache::extend('secure_file', function ($app, $config) {
            return Cache::repository(new SecureFileStore($app->make('Illuminate\Filesystem\Filesystem'),
                $config['path']));
        });
    }
}
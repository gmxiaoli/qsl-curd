<?php

namespace Gmxiaoli\Curd\Providers;

use Gmxiaoli\Curd\Commands\G;
use Illuminate\Support\ServiceProvider;

/**
 * 配置文件发布
 */
class GenConfigPublishProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('gmxiaoli.generator', function ($app) {
            return new G();
        });
        $this->commands([
            'gmxiaoli.generator'
        ]);
    }
}

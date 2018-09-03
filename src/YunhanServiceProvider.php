<?php

namespace Yunhan;

use Illuminate\Support\ServiceProvider;
use Yunhan\Rbac\Commands\MenuHelper;
use Yunhan\Rbac\Middleware\RbacAuthCheck;

/**
 * @property \Laravel\Lumen\Application $app
 * Class YunhanServiceProvider
 * @package Yunhan
 */
class YunhanServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->routeMiddleware([
            'rbacCheck' => RbacAuthCheck::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands(
                MenuHelper::class
            );
        }
    }

}

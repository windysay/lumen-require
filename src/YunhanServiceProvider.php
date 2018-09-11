<?php

namespace Yunhan;

use Illuminate\Support\ServiceProvider;
use Yunhan\Rbac\Commands\createMenuFile;
use Yunhan\Rbac\Commands\MenuHelper;
use Yunhan\Rbac\Middleware\RbacAuthCheck;
use Yunhan\Rbac\Middleware\SetGuard;

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
            'setGuard' => SetGuard::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MenuHelper::class,
                createMenuFile::class
            ]);
        }
    }

    public function boot()
    {
        //重新绑定Router
        $routes = app('router')->getRoutes();
        $this->app->singleton('router', function () use ($routes) {

            $router = new \Yunhan\Rbac\Routing\Router($this->app);
            //把已加载的路由绑定到新的对象上面
            foreach ($routes as $route) {
                $router->addRoute($route['method'], $route['uri'], $route['action']);
            }
            return $router;
        });
        $this->app->router = $this->app->make('router');
    }

}

<?php

namespace Yunhan\Rbac\Tests;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Yunhan\Rbac\Tests\App\Auth\User;

abstract class BaseTestCase extends \Laravel\Lumen\Testing\TestCase
{
    use DatabaseTransactions;

    const BASE_DIR = __DIR__;

    const DEFAULT_GUARD = 'admin';

    /**
     * Creates the application.
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        require_once static::BASE_DIR . '/../../../vendor/autoload.php';

        try {
            (new \Dotenv\Dotenv(static::BASE_DIR))->load();
        } catch (\Dotenv\Exception\InvalidPathException $e) {
            //
        }

        /*
        |--------------------------------------------------------------------------
        | Create The Application
        |--------------------------------------------------------------------------
        |
        | Here we will load the environment and create the application instance
        | that serves as the central piece of this framework. We'll use this
        | application as an "IoC" container and router for this framework.
        |
        */

        $app = new \Laravel\Lumen\Application(
            realpath(static::BASE_DIR . '/../')
        );

        $app->withFacades();

        $app->withEloquent();

        /*
        |--------------------------------------------------------------------------
        | Register Container Bindings
        |--------------------------------------------------------------------------
        |
        | Now we will register a few bindings in the service container. We will
        | register the exception handler and the console kernel. You may add
        | your own bindings here if you like or you can make another file.
        |
        */

        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Laravel\Lumen\Exceptions\Handler::class
        );

        /*
        |--------------------------------------------------------------------------
        | Register Middleware
        |--------------------------------------------------------------------------
        |
        | Next, we will register the middleware with the application. These can
        | be global middleware that run before and after each request into a
        | route or middleware that'll be assigned to some specific routes.
        |
        */

        // $app->middleware([]);


        config([
            'permission.table_names' => [
                'roles' => 'new_roles',
                'permissions' => 'new_permissions',
                'model_has_permissions' => 'new_model_has_permissions',
                'model_has_roles' => 'new_model_has_roles',
                'role_has_permissions' => 'new_role_has_permissions',
                'menus' => 'new_menus',
                'role_has_menus' => 'new_role_has_menus',
            ],
            'permission.route_params.' . static::DEFAULT_GUARD => [
                'namespace' => '\Yunhan\Rbac\Controllers',
                'middleware' => [],
                'prefix' => '',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Register Service Providers
        |--------------------------------------------------------------------------
        |
        | Here we will register all of the application's service providers which
        | are used to bind services into the container. Service providers are
        | totally optional, so you are not required to uncomment this line.
        |
        */

        // $app->register(App\Providers\AppServiceProvider::class);
        $app->register(\Yunhan\Rbac\Providers\RbacServiceProvider::class);
        $app->register(\Yunhan\Rbac\Tests\App\Providers\AuthServiceProvider::class);

        config([
            'auth' => [
                'defaults' => [
                    'guard' => static::DEFAULT_GUARD,
                ],
                'guards' => [
                    static::DEFAULT_GUARD => [
                        'driver' => 'token',
                        'provider' => static::DEFAULT_GUARD,
                    ],
                ],
                'providers' => [
                    static::DEFAULT_GUARD => [
                        'driver' => 'eloquent',
                        'model' => User::class,
                    ],
                ],
            ],
        ]);

        return $app;
    }
}
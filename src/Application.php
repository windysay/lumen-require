<?php

namespace Yunhan;

class Application extends \Laravel\Lumen\Application
{
    /**
     * Register container bindings for the application.
     * @return void
     */
    protected function registerDatabaseBindings()
    {
        $this->singleton('db', function () {
            return $this->loadComponent(
                'database',
                [
                    'Illuminate\Database\DatabaseServiceProvider',
                    'Yunhan\Providers\PaginationServiceProvider',
                ],
                'db'
            );
        });
    }
}

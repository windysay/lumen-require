<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/8/7
 * Time: 10:19
 */

namespace Yunhan\Rbac\Providers;

use Illuminate\Support\ServiceProvider;
use Yunhan\Rbac\Contracts\MenuContract;
use Yunhan\Rbac\Contracts\OutputDataFormatContract;


class RbacServiceProvider extends ServiceProvider
{
    public function register()
    {
        //注册依赖
        $this->app->register(\Spatie\Permission\PermissionServiceProvider::class);
    }

    public function boot()
    {
        $this->registerModelBindings();
    }

    protected function registerModelBindings()
    {

        $this->app->bind(MenuContract::class, config('permission.models.menu'));

        //定义输出格式
        $this->app->bind(OutputDataFormatContract::class, config('permission.output'));
    }
}
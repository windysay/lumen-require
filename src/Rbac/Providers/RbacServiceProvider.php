<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/8/7
 * Time: 10:19
 */

namespace Yunhan\Rbac\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Routing\Router;
use Spatie\Permission\PermissionServiceProvider;
use Yunhan\Rbac\Contracts\MenuContract;
use Yunhan\Rbac\Contracts\OutputDataFormatContract;
use Yunhan\Rbac\Middleware\RbacMiddleware;


class RbacServiceProvider extends ServiceProvider
{
    public function register()
    {

        //1.加载配置文件
        $this->mergeConfigFrom(__DIR__ . '/../config/permission.php', 'permission');

        //2.注册依赖
        $this->app->register(PermissionServiceProvider::class);

        //3.注册中间件
        $this->app->routeMiddleware([
            'RBAC' => RbacMiddleware::class,
        ]);
    }

    public function boot()
    {
        $this->registerModelBindings();


        if (config('permission.use_default_route')) {

            //获取需要加载的guard
            $guards = config('permission.route_guard');
            foreach ($guards as $guard) {
                //注册路由文件
                $key = "permission.route_params";
                if (!array_key_exists($guard, config($key))) {
                    throw new \Exception("guard`$guard`不存在,请在config/permission.php文件设置route_parmas文件");
                }
                $key .= '.' . $guard;
                $params = [
                    'namespace' => config("$key.namespace"),
                    'prefix' => config("$key.prefix"),
                    'middleware' => config("$key.middleware"),
                ];

                $this->app['router']->group($params, function (Router $router) {
                    $path = __DIR__ . '/../routes/';

                    /*
                     * 管理员
                     */
                    //目录管理
                    require $path . 'menu.php';
                    //后台操作权限
                    require $path . 'permission.php';
                    //用户权限组
                    require $path . 'role.php';
                });

                //不需要权限认证,去掉RBAC
                $key = array_search('RBAC', $params['middleware']);
                if ($key !== false) {
                    unset($params['middleware'][$key]);
                }
                $this->app['router']->group($params, function (Router $router) {
                    $path = __DIR__ . '/../routes/';
                    //根据角色获取菜单列表
                    $router->get('menu/menuList', 'MenuController@menuList');
                });
            }

        }

    }

    protected function registerModelBindings()
    {

        $this->app->bind(MenuContract::class, config('permission.models.menu'));

        //定义输出格式
        $this->app->bind(OutputDataFormatContract::class, config('permission.output'));
    }
}
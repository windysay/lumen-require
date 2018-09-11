<?php

namespace Yunhan\Rbac\Commands;

use Illuminate\Console\Command;
use Yunhan\Rbac\Contracts\Config;
use Yunhan\Rbac\Utils\RbacHelper;

class MenuHelper extends Command
{
    /**
     * 菜单文件
     *
     * @var string
     */
    protected $menuPath;

    /**
     * @var string
     */
    protected $module;

    /**
     * 控制台命令名称
     * @var string
     */
    protected $signature = 'rbac-menu:generate 
                                {module : 同步的模块.对应中间件加的gurade:例如`"middleware" => "setGuard:admin"`模块为`admin`}';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '菜单生成';

    /**
     * @var string
     */
    protected $parentPath = '';

    /**
     * @var string
     */
    protected $action = '/api/rbac/menu/sync-menus';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->module = $this->argument('module');
        $this->menuPath = storage_path("menus/{$this->module}.php");
        if (!file_exists($this->menuPath)) {
            throw new \Exception('菜单文件不存在');
        }

        $menus = $this->getMenus();
        $routes = $this->getRoutes();

        //把权限添加在子菜单下面
        $this->combineMenuAction($menus, $routes);

        /** @var Config $config */
        $config = app(Config::class);

        $data = [
            'menu' => [
                'new' => $menus,
                'update' => [],
            ],
            'app_key' => $config->getAppKey(),
            'guard' => $this->module,
        ];

        $data['sign'] = $config->getSign($data);
        $url = rtrim($config->getDomain(), '/') . $this->action;

        $result = RbacHelper::curlPost($url, $data);

        if (isset($result['code']) && $result['code'] == 18000) {
            echo 'success';
        }

        echo $result['msg'];

    }

    /**
     * 获取菜单文件
     *
     * @return mixed
     */
    protected function getMenus()
    {
        return include $this->menuPath;
    }

    /**
     * 获取路由
     *
     * @return array
     */
    protected function getRoutes()
    {
        $routes = app('router')->getRoutes();
        $filterRoutes = array_filter($routes, function ($item) {
            if (isset($item['action']['middleware'])) {
                $middlewares = $item['action']['middleware'];
                foreach ($middlewares as $middleware) {
                    if (strpos($middleware, $this->module) !== false) {
                        return true;
                    }
                }
            }

            return false;
        });

        $groupRoutes = [];
        foreach ($filterRoutes as $k => $route) {
            $path = $route['action']['path'] ?? false;
            if (!$path) {
                //过滤没有path属性的路由
                continue;
            }

            if (array_key_exists($path, $groupRoutes)) {
                $groupRoutes[$path][] = $k;
            } else {
                $groupRoutes[$path] = [];
                $groupRoutes[$path][] = $k;
            }
        }

        return $groupRoutes;
    }

    /**
     * @param array $menus
     * @param array $routes
     */
    protected function combineMenuAction(&$menus, &$routes)
    {
        if (is_array($menus)) {
            foreach ($menus as &$menu) {
                if (isset($menu['children']) && is_array($menu['children'])) {
                    $this->parentPath = $this->combinePath($menu['path']);
                    $this->combineMenuAction($menu['children'], $routes);
                    $this->replacePath($menu['path']);
                } else {
                    $path = $this->combinePath($menu['path']);
                    if (array_key_exists($path, $routes)) {
                        $menu['actions'] = $routes[$path];
                    }
                }
            }
        }
    }

    /**
     * @param static $path
     * @return string
     */
    protected function combinePath($path)
    {
        if ($this->parentPath) {
            return $this->parentPath . '.' . $path;
        }
        return $this->module . '.' . $path;
    }

    /**
     * @param string $path
     */
    protected function replacePath($path)
    {
        if (strpos($this->parentPath, '.' . $path) !== false) {
            $this->parentPath = str_replace('.' . $path, '', $this->parentPath);
        } else {
            $this->parentPath = str_replace($path, '', $this->parentPath);
        }
    }
}
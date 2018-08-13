### 配置文件

复制文件
```
//权限配置文件
cp vendor/yunhanphp/lumen-require/src/Rbac/config/permission.php config/permission.php
//数据库迁移文件
cp vendor/yunhanphp/lumen-require/src/Rbac/migrations/create_permission_tables.php database/migrations/2018_01_01_000000_create_permission_tables.php
```

在`bootstrap/app.php`注册中间件
```
$app->routeMiddleware([
    'AdminRbac' => \Yunhan\Rbac\Middleware\RbacMiddleware::class,
]);
```

添加配置文件和provider
```
$app->configure('permission');
$app->register(\Yunhan\Rbac\Providers\RbacServiceProvider::class);
```

执行迁移文件
```
php artisan migrate
```

### 使用

添加RbacMiddleware中间件到路由
```
$params = [
    'prefix' => 'admin',
    'namespace' => 'Admin\Controllers',
    'middleware' => [..., 'AdminRbac']
];
$router->group($params, function (Router $api) {

}
```

添加路由文件
```
$params['namespace'] = '\Yunhan\Rbac\Controllers';
$params['middleware'] = [..., 'AdminRbac'];
$router->group($params, function (Router $router) {

    $path = base_path('vendor/yunhanphp/lumen-require/src/Rbac/routes') . '/';

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
```


添加 Spatie\Permission\Traits\HasRoles trait 到你的 User model(s):
```
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    // ...
}
```
添加用户角色
```
use \Yunhan\Rbac\Models\Role;
//role_ids是角色的id用逗号分隔
$roleIds = explode(',', $data['role_ids']);
if ($roleIds) {
    //查找角色
    $roles = Role::whereIn('id', $roleIds)->get();
    //添加角色
    $user->assignRole($roles);
}
```

用户编辑角色
```
use \Yunhan\Rbac\Models\Role;
//role_ids是角色的id用逗号分隔
$roleIds = explode(',', $data['role_ids']);
//更新角色
if ($roleIds) {
    $roles = Role::whereIn('id', $roleIds)->get();
    $user->syncRoles($roles);
}
```

删除用户角色
```
//删除角色($user->roles是用户所有角色)
foreach ($user->roles as $role) {
    $user->removeRole($role);
}
```


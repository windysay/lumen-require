### 配置文件

复制文件
```
//如果需要自定义一些配置复制permission.php文件到config目录
cp vendor/yunhanphp/lumen-require/src/Rbac/config/permission.php config/permission.php

//数据库迁移文件
cp vendor/yunhanphp/lumen-require/src/Rbac/migrations/create_permission_tables.php database/migrations/2018_01_01_000000_create_permission_tables.php
```


添加配置文件和provider
```
//$app->configure('permission');
$app->register(\Yunhan\Rbac\Providers\RbacServiceProvider::class);
```

执行迁移文件
```
php artisan migrate
```

### 使用

添加RbacMiddleware中间件`RBAC`到路由
```
$params = [
    'prefix' => 'admin',
    'namespace' => 'Admin\Controllers',
    'middleware' => [..., 'RBAC']
];
$router->group($params, function (Router $api) {

}
```


添加 Yunhan\Rbac\Traits\AssignRole trait 到你的 User model(s):
```
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use AssignRole;

    // ...
}

```
添加用户角色.`$user为添加用户对应的model`
```
//添加角色
/**
 * @var array $roleIds
 * @var static $user
 */
$user->assignRoleToUser($roleIds);
```

用户编辑角色.`$user为当前编辑的对象`
```
//编辑角色
/**
 * @var array $roleIds
 * @var static $user
 */
$user->assignRoleToUser($roleIds);
```

删除用户角色.`$user为当前删除的对象`
```
//删除角色
$user->removeAllRoles();
```

权限说明
```
后台对应的每一个路由都是一个权限,存在permission表中.

把需要权限认证的接口路由加上 RBAC 中间件:
//需要权限认证
$router->group([...,'middleware'=>[...,'RBAC']], function (Router $api) {
    //游客列表
    require 'user/clientList.php';
    //旅行社列表
    require 'user/travelList.php';
    ...
}

//不需要权限认证
$router->group([...], function (Router $api) {
    /*
     * 公共接口
     */
    require 'common/common.php';
}

储存格式为:请求方法+请求接口(GET api/admin/user/getUserList)
如果存在这种形式路由需要设置别名
    /api/article/1
    $router->get('api/article/{id}', 'ArticleController@show');
应该设置为
    $router->get('api/article/{id}', ['as'=>'article.id','uses'=>ArticleController@show]);
    as别名可以随便设置,权限存 `GET api/article/{id}`
    
user id为1的用户(超级管理员)拥有所有权限

```

`权限使用的是laravel-permission扩展,具体使用可以参考laravel-permission文档`
- [laravel-permission](https://github.com/spatie/laravel-permission)

## todo
- [ ] 权限分组
- [ ] 父级权限保存
- [ ] 页面权限需要前端做对应过滤


<?php

namespace Yunhan\Rbac\Traits;

use Illuminate\Http\Request;
use Spatie\Permission\Traits\HasRoles;
use Yunhan\Rbac\Models\Permission;

trait AssignRole
{
    use HasRoles;

    public function checkPermission(Request $request, $userRoles)
    {
        $path = $request->path();
        //查看是否设置别名,兼容 test/{id} 这种路由
        // @phan-suppress-next-line PhanTypeMismatchDimFetch
        if (isset($request->route()[1]['as'])) {
            // @phan-suppress-next-line PhanTypeMismatchDimFetch
            $alias = $request->route()[1]['as'];
            //通过别名获取路由
            $path = str_replace_first('/', '', app('router')->namedRoutes[$alias]);
        }

        $uri = Permission::combineMethodUri($request->method(), $path);
        $permission = Permission::findByName($uri);
        //获取菜单对应的角色
        $roles = $permission->menu()->roles;


    }
}
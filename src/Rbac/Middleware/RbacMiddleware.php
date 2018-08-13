<?php

namespace Yunhan\Rbac\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Yunhan\Rbac\Models\Permission;

class RbacMiddleware
{
    //免认证白名单
    protected $whitelist = [];

    public function __construct()
    {
        $guard = config('auth.defaults.guard');
        $whitelist = config('permission.white_list');
        if (!array_key_exists($guard, $whitelist)) {
            throw new \Exception('权限白名单不存在');
        }

        $this->whitelist = $whitelist[$guard];
    }

    public function handle(Request $request, Closure $next)
    {

        if (in_array($request->path(), $this->whitelist)) {
            return $next($request);
        }

        $user = Auth::user();
        //超级管理员不验证权限
        if ($user->id == 1) {
            return $next($request);
        }

        try {
            $path = $request->path();
            //查看是否设置别名,兼容 test/{id} 这种路由
            // @phan-suppress-next-line PhanTypeMismatchDimFetch
            if (isset($request->route()[1]['as'])) {
                // @phan-suppress-next-line PhanTypeMismatchDimFetch
                $alias = $request->route()[1]['as'];
                //通过别名获取路由
                $path = str_replace_first('/', '', app('router')->namedRoutes[$alias]);
            }

            $request->getRouteResolver();
            $uri = Permission::combineMethodUri($request->method(), $path);
            $hasPermission = $user->hasPermissionTo($uri);
            if (!$hasPermission) {
                abort(403, '该角色无权限');
            }

        } catch (\Exception $exception) {
            abort(403, '该角色无权限');
        }

        return $next($request);
    }
}
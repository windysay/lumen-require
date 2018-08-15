<?php

namespace Yunhan\Rbac\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class RbacMiddleware
{

    public function __construct()
    {
        $guard = config('auth.defaults.guard');
        $whitelist = config('permission.white_list');
        if (array_key_exists($guard, $whitelist)) {
            $this->whitelist = $whitelist[$guard];
        }

    }

    public function handle(Request $request, Closure $next)
    {

        //判断权限是否加入对应菜单下面.如果没有加入默认是白名单不需要判断

        $user = Auth::user();
        //超级管理员不验证权限
        if ($user->id == 1) {
            return $next($request);
        }

        try {
            //获取用户角色
            $rolesIds = $user->roles
                ->pluck('id')
                ->toArray();
            if ($user->checkPermission($request, $rolesIds)) {
                return $next($request);
            } else {
                abort(403, '该用户无权限');
            }

        } catch (\Exception $exception) {
            abort(403, '该用户无权限');
        }

    }
}
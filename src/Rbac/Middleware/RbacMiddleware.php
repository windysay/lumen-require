<?php

namespace Yunhan\Rbac\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class RbacMiddleware
{

    public function handle(Request $request, Closure $next)
    {

        $user = Auth::user();

        //查看是否拥有超级权限(roleId为1)
        if ($user->isSumperAdmin()) {
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

        } catch (PermissionDoesNotExist $exception) {
            abort(403, '权限不存在,请检查是否添加了权限');
        } catch (\Exception $exception) {
            abort(403, '该用户无权限');
        }

    }
}
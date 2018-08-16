<?php

namespace Yunhan\Rbac\Traits;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;
use Yunhan\Rbac\Models\Permission;
use Yunhan\Rbac\Models\Role;

trait AssignRole
{
    use HasRoles;

    /**
     * 验证用户权限
     * @param Request $request
     * @param $userRoleIds
     * @return bool
     */
    public function checkPermission(Request $request, $userRoleIds)
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
        //判断是否是公共权限(menu_id = 0)
        if ($permission->menu_id == 0) {
            return true;
        }
        //获取菜单对应的角色
        $roleIds = $permission->menu
            ->roles
            ->pluck('id')
            ->toArray();

        //判断用户权限
        foreach ($roleIds as $roleId) {
            if (in_array($roleId, $userRoleIds, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 给角色赋值
     * @param array $roleIds
     * @param Authorizable $user
     */
    public function assignRoleToUser(array $roleIds)
    {
        $roleIds = array_filter($roleIds);
        if ($roleIds) {
            //查找角色
            $roles = Role::whereIn('id', $roleIds)->get();
            //添加角色
            $this->assignRole($roles);
        }
    }

    /**
     * 清除用户所有角色
     * @param Authorizable $user
     */
    public function removeAllRoles()
    {
        foreach ($this->roles as $role) {
            $this->removeRole($role);
        }
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getMenusViaRoles(): Collection
    {
        return $this->load('roles', 'roles.menus')
            ->roles->flatMap(function ($role) {
                return $role->menus;
            })->sort()->values();
    }

    /**
     * 判断是否是超级管理员
     * @param Authorizable $user
     * @return bool
     */
    public function isSumperAdmin(): bool
    {
        //获取用户角色
        $rolesIds = $this->roles
            ->pluck('id')
            ->toArray();

        //管理员 id=1不验证
        if ($this->id == 1) {
            return true;
        }

        //查看是否拥有超级权限(roleId为1)
        if (in_array(config('permission.super_admin'), $rolesIds, true)) {
            return true;
        }

        return false;
    }
}
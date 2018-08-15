<?php

namespace Yunhan\Rbac\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Role as BaseRole;

/**
 * Yunhan\Rbac\Models\Role
 *
 * @property int $id
 * @property string $name 角色名称
 * @property string $guard_name 守卫,区分模块.比如:前端(web),后端(admin)
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Yunhan\Rbac\Models\Permission[] $permissions
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\Permission\Models\Role permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends BaseRole
{

    public $guarded = ['*'];
    protected $fillable = ['name', 'guard_name', 'created_at', 'updated_at'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('permission.connection');
    }

    /**
     * 给重新角色赋值
     * @param $roleIds
     * @return mixed
     */
    public static function assignRoleToUser($roleIds)
    {
        $roles = static::whereIn('id', $roleIds)->get();
        $user = Auth::user();
        //重新赋值角色
        return $user->syncRoles($roles);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('guard', function (Builder $builder) {
            $table = config('permission.table_names.roles');
            $builder->where("{$table}.guard_name", Guard::getDefaultName(static::class));
        });
    }

    /**
     * 角色类表
     * @return mixed
     */
    public function list()
    {
        $lists = static::withCount('users')->get();
        return $lists;
    }

    /**
     * 获取角色和权限
     * @param $id
     * @return Builder|\Illuminate\Database\Eloquent\Model|null|object|Role
     */
    public function findWithPermissions($id)
    {
        return static::with('permissions')
            ->whereKey($id)
            ->first();
    }

    /**
     * 通过角色Id获取菜单分组的权限列表
     * @param array $roleIds
     * @return array
     */
    public function findWithPermissionByRole(array $roleIds)
    {
        $roles = static::with(['permissions' => function ($query) {
            $query->select(['id as permissionId']);
        }])
            ->whereIn('id', $roleIds)
            ->get()
            ->toArray();
        $data = [];
        foreach ($roles as $role) {
            //获取对应的permissionIds
            $permiddionIds = [];
            array_walk($role['permissions'], function ($v, $k) use (&$permiddionIds) {
                array_push($permiddionIds, $v['permissionId']);
            });
            $data[] = [
                'name' => $role['name'],
                'data' => (new Permission())->listByInIds($permiddionIds),
            ];
        }

        return $data;
    }

    /**
     * 删除角色
     * @param $id
     * @return bool|null
     */
    public function destory($id)
    {
        //这里要使用Model的delete方法,触发删除事件清除缓存.同时这里也会把关联的中间表删掉
        /** @var static $role */
        $role = static::findById((int)$id);
        return $role->delete();
    }

    /**
     * 添加角色
     * @param $data
     * @return Role
     */
    public function add($data)
    {
        $permissionIds = explode(',', $data['permission_ids']);
        //去掉负数的id,负数id全部是菜单Id
        $permissionIds = static::filterRoleIds($permissionIds);
        /** @var static $role */
        $role = static::create($data);
        $result = $role->givePermissionTo($permissionIds);

        return $role;

    }

    /**
     * 去掉菜单列表(在菜单分组的权限列表,菜单Id全是负数)
     * @param array $ids
     * @return array
     */
    public static function filterRoleIds(array $ids)
    {
        //去掉负数的id,负数id全部是菜单Id
        return array_filter($ids, function ($v) {
            return $v > 0;
        });
    }

    /**
     * 编辑角色
     * @param $data
     * @return Role
     */
    public function edit($data)
    {
        /** @var static $role */
        $role = static::findById($data['id']);
        $role->name = $data['name'];
        $role->save();

        $permissionIds = explode(',', $data['permission_ids']);
        //去掉负数的id,负数id全部是菜单Id
        $permissionIds = static::filterRoleIds($permissionIds);
        //更新权限
        $result = $role->syncPermissions($permissionIds);

        return $role;

    }

}
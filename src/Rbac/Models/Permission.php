<?php

namespace Yunhan\Rbac\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Permission as BasePermission;

/**
 * Yunhan\Rbac\Models\Permission
 *
 * @property int $id
 * @property string $remark 描述
 * @property string $name 请求方式+路由
 * @property int $menu_id 菜单ID
 * @property string $guard_name 守卫,区分模块.比如:前端(web),后端(admin)
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Yunhan\Rbac\Models\Permission[] $children
 * @property-read \Yunhan\Rbac\Models\Menu $menu
 * @property-read \Yunhan\Rbac\Models\Permission $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Yunhan\Rbac\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Yunhan\Rbac\Models\Role[] $roles
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\Permission\Models\Permission permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\Permission\Models\Permission role($roles)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Permission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Permission whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Permission whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permission extends BasePermission
{
    const REQUEST_METHOND = [
        1 => 'POST',
        2 => 'GET',
        3 => 'PUT',
        4 => 'PATCH',
        5 => 'DELETE',
    ];

    public $guarded = ['*'];

    protected $fillable = ['remark', 'name', 'parent_id', 'menu_id', 'guard_name', 'created_at', 'updated_at'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('permission.connection');

    }

    public static function findManyByInIds($ids)
    {
        return static::whereIn('id', $ids)->get();
    }

    public static function getRequestMethondList()
    {
        return static::REQUEST_METHOND;
    }

    public static function processName($methodId, $uri)
    {
        $methodName = static::getRequestMethond($methodId);
        if (!$methodName) {
            return false;
        }
        return static::combineMethodUri($methodName, $uri);
    }

    public static function getRequestMethond($id)
    {
        if (array_key_exists($id, static::REQUEST_METHOND)) {
            return static::REQUEST_METHOND[$id];
        }
        return false;
    }

    public static function combineMethodUri($method, $uri)
    {
        return $method . ' ' . $uri;
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('guard', function (Builder $builder) {
            $table = config('permission.table_names.permissions');
            $builder->where("{$table}.guard_name", Guard::getDefaultName(static::class));
        });
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(static::class, 'parent_id', 'id');
    }

    public function list()
    {
        $lists = static::select(['menu_id'])
            ->get()
            ->pluck('menu_id')
            ->toArray();

        $menuIds = array_unique($lists);

        return $this->getPermissionWithMenu($menuIds);
    }

    public function getPermissionWithMenu($menuIds)
    {

        $menuLists = Menu::whereIn('id', $menuIds)
            ->get();

        //获取所有菜单
        $allMenus = Menu::select(['id', 'name', 'path', 'icon', 'parent_id', 'sort'])
            ->get()
            ->toArray();

        $sortMenulist = [];
        foreach ($menuLists as $menuList) {
            Menu::$sortedTree = [];
            $parentMenus = Menu::getParentMenus($allMenus, $menuList->id);
            $lastIndex = count($parentMenus) - 1;
            $parentMenus[$lastIndex]['children'] = static::select(['id as key', 'remark as title', 'name'])
                ->where(['menu_id' => $menuList->id])
                ->orderBy('name', 'ASC')
                ->get()
                ->toArray();
            //把相同parentId的合并,$parentMenus[0]是顶级菜单的Id
            // @phan-suppress-next-line PhanTypeInvalidDimOffset
            if (array_key_exists($parentMenus[0]['id'], $sortMenulist)) {
                // @phan-suppress-next-line PhanTypeInvalidDimOffset
                array_push($sortMenulist[$parentMenus[0]['id']]['children'],
                    ...static::getTree($parentMenus, $menuList->id)[0]['children']);
            } else {
                // @phan-suppress-next-line PhanTypeInvalidDimOffset
                $sortMenulist[$parentMenus[0]['id']] = static::getTree($parentMenus, $menuList->id)[0];
            }

        }

        //排序
        usort($sortMenulist, function ($a, $b) {
            return ($a['sort'] < $b['sort']) ? -1 : 1;
        });

        return $sortMenulist;
    }

    /**
     * 生成菜单无限极分类
     * @param array $data
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    public static function getTree($data, $menuId, $parent_id = 0, $level = 0)
    {
        $tree = [];
        if ($data && is_array($data)) {
            foreach ($data as $v) {
                if ($v['parent_id'] == $parent_id) {
                    $tree[] = [
                        //跟permissionId区分开(菜单Id全是负数)
                        'key' => -$v['id'],
                        'level' => $level,
                        'title' => $v['name'],
                        'name' => $v['name'],
                        'sort' => $v['sort'],
                        //最后一个不生成children
                        'children' => ($v['id'] == $menuId)
                            ? $v['children']
                            : static::getTree($data, $menuId, $v['id'], $level + 1),
                    ];
                }
            }
        }
        return $tree;
    }

    public function listByInIds(array $ids)
    {
        $lists = static::select(['menu_id'])
            ->whereIn('id', $ids)
            ->get()
            ->pluck('menu_id')
            ->toArray();

        $menuIds = array_unique($lists);

        return $this->getPermissionWithMenu($menuIds);
    }

    public function add($data)
    {
        $this->checkMenu($data['menu_id']);
        return static::create($data);
    }

    private function checkMenu($menuId)
    {
        if (Menu::where(['id' => $menuId])->value('parent_id') === 0) {
            throw new \Exception('不能选择顶级菜单');
        }

        if (Menu::where(['parent_id' => $menuId])->count() > 0) {
            throw new \Exception('菜单下面有子菜单,无法选择');
        }
    }

    public function edit($permissionId, $data)
    {
        $this->checkMenu($data['menu_id']);
        /** @var static $permission */
        $permission = static::findById((int)$permissionId);
        return $permission->fill($data)->save();
    }

    public function destory($id)
    {
        //查看是否有子节点
        /** @var static $permission */
        $permission = static::findById((int)$id);
        //这里要使用Model的delete方法,触发删除事件清除缓存.同时这里也会把关联的中间表删掉
        return $permission->delete();

    }

    public function detail($id)
    {
        $permission = Permission::findById((int)$id);
        // @phan-suppress-next-line PhanUndeclaredProperty
        $methodName = explode(' ', $permission->name);
        $methodId = static::getRequestMethondId(trim($methodName[0]));
        // @phan-suppress-next-line PhanUndeclaredProperty
        $permission->method_id = $methodId;
        // @phan-suppress-next-line PhanUndeclaredProperty
        $permission->name = trim($methodName[1]);
        return $permission;
    }

    public static function getRequestMethondId($methodName)
    {
        foreach (static::REQUEST_METHOND as $id => $method) {
            if ($method == $methodName) {
                return $id;
            }
        }
        return false;
    }

}
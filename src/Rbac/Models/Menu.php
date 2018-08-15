<?php

namespace Yunhan\Rbac\Models;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Guard;
use Yunhan\Rbac\Contracts\MenuContract;
use Yunhan\Rbac\Exceptions\MenuDoesNotExist;
use Yunhan\Rbac\Utils\Helper;

/**
 * Yunhan\Rbac\Models\Menu
 *
 * @property int $id
 * @property int $parent_id 父Id
 * @property string $name
 * @property int $is_show 是否显示菜单 1显示 0不显示 默认显示
 * @property string $guard_name 守卫,区分模块.比如:前端(web),后端(admin)
 * @property string $path 前端扩展用,可以写菜单对应的前端的路由
 * @property string $icon 图标
 * @property int $sort
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Yunhan\Rbac\Models\Menu[] $children
 * @property-read \Yunhan\Rbac\Models\Menu $parent
 * @property-read \Yunhan\Rbac\Models\Permission $permission
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu whereIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Yunhan\Rbac\Models\Menu whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Menu extends Model implements MenuContract
{
    public static $sortedTree = [];
    protected $fillable = ['parent_id', 'name', 'path', 'icon', 'sort', 'guard_name', 'created_at', 'updated_at'];

    public function __construct(array $attributes = [])
    {

        $this->connection = config('permission.connection');

        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.menus'));
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('guard', function (Builder $builder) {
            $table = config('permission.table_names.menus');
            $builder->where("{$table}.guard_name", Guard::getDefaultName(static::class));
        });
    }

    /**
     * 通过角色获取用户菜单
     * @param Authorizable $user
     * @return array
     */
    public static function findUserMenuByRole(Authorizable $user)
    {

        //超级管理员获取所有权限
        // @phan-suppress-next-line PhanUndeclaredProperty
        if ($user->isSumperAdmin()) {
            $menus = Menu::all();
        } else {
            /** @var \Illuminate\Support\Collection $menus */
            // @phan-suppress-next-line PhanUndeclaredMethod
            $menus = $user->getMenusViaRoles();
        }
        //查找菜单,只查找可以显示的菜单
        $menuIds = $menus
            ->pluck('id')
            ->toArray();
        $select = ['id', 'name', 'path', 'icon', 'parent_id', 'sort'];
        return static::getMenuTreebyInId($menuIds, $select);
    }

    /**
     * 根据菜单Id获取父菜单
     * @param array $menuIds
     * @param array $select
     * @return array
     */
    public static function getMenuTreebyInId(array $menuIds, $select = ['*'])
    {
        //查找菜单
        $menuIds = array_unique($menuIds);
        $menuLists = static::whereIn('id', $menuIds)
            //只查找要显示的菜单
            ->where(['is_show' => 1])
            ->get();

        //获取所有菜单
        $allMenus = Menu::select($select)
            ->get()
            ->toArray();

        $sortMenulist = [];
        foreach ($menuLists as $menuList) {
            static::$sortedTree = [];
            $parentMenus = static::getParentMenus($allMenus, $menuList->id);
            //把相同parentId的合并,$parentMenus[0]是顶级菜单的Id
            if (array_key_exists($parentMenus[0]['id'], $sortMenulist)) {
                array_push($sortMenulist[$parentMenus[0]['id']]['children'],
                    ...static::getTree($parentMenus)[0]['children']);
            } else {
                $sortMenulist[$parentMenus[0]['id']] = static::getTree($parentMenus)[0];
            }
        }

        //排序
        usort($sortMenulist, function ($a, $b) {
            return ($a['sort'] < $b['sort']) ? -1 : 1;
        });

        //清除空children
        Helper::cleanNullChildren($sortMenulist);

        return $sortMenulist;
    }

    /**
     * 通过子节点获取所有父节点
     * @param array $data
     * @param int $id
     * @param int $level
     * @return array
     */
    public static function getParentMenus($data = [], $id = 0, $level = 0)
    {
        if ($data && is_array($data)) {
            foreach ($data as $v) {
                if ($v['id'] == $id) {
                    $tree = [
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'path' => $v['path'],
                        'icon' => $v['icon'],
                        'sort' => $v['sort'],
                        'parent_id' => $v['parent_id'],
                    ];

                    array_unshift(static::$sortedTree, $tree);
                    static::getParentMenus($data, $v['parent_id'], $level - 1);
                }
            }
        }

        return static::$sortedTree;
    }

    /**
     * 无限极分类,树状结构
     * @param array $data
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    public static function getTree($data = [], $parent_id = 0, $level = 0)
    {
        $tree = [];
        if ($data && is_array($data)) {
            foreach ($data as $v) {
                if ($v['parent_id'] == $parent_id) {
                    $tree[] = [
                        'id' => $v['id'],
                        'key' => $v['id'],
                        'name' => $v['name'],
                        'title' => $v['name'],
                        'path' => $v['path'],
                        'icon' => $v['icon'],
                        'sort' => $v['sort'],
                        'parent_id' => $v['parent_id'],
                        'children' => static::getTree($data, $v['id'], $level + 1),
                    ];
                }
            }
        }
        return $tree;
    }

    public function permission()
    {
        return $this->hasOne(Permission::class, 'menu_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(static::class, 'parent_id', 'id');
    }

    /**
     * A menu can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_menus')
        );
    }


    /**
     * 获取格式化菜单列表
     * @return \Illuminate\Support\Collection
     */
    public function formatList($showHidden = 0)
    {
        $data = static::select(['id', 'name', 'path', 'icon',
            'parent_id', 'created_at', 'updated_at', 'sort'])
            //判断是否显示隐藏菜单
            ->when($showHidden == 0, function ($query) {
                $query->where(['is_show' => 1]);
            })
            ->get()
            ->toArray();

        //无限极分类
        $menuTree = static::getTree($data);

        //排序
        usort($menuTree, function ($a, $b) {
            return ($a['sort'] < $b['sort']) ? -1 : 1;
        });

        Helper::cleanNullChildren($menuTree);

        return $menuTree;
    }

    public function findMenu($id)
    {
        return static::select(['id', 'name', 'path', 'icon', 'created_at', 'updated_at', 'parent_id', 'sort'])
            ->where('id', $id)
            ->first();
    }

    /**
     * 创建或者更新菜单
     * @param $data
     * @param null $id
     * @return mixed
     * @throws \Exception
     */
    public function createOrUpdateMenu($data, $id = null)
    {

        if ($id && isset($data['parent_id']) && $data['parent_id']) {
            $all = static::select(['id', 'parent_id'])
                ->get()
                ->toArray();
            //获取所有子节点
            $childrenIds = static::getChildrenByParentId($all, $id);
            if (in_array($data['parent_id'], $childrenIds)) {
                throw new \Exception('上级目录不能是自己的子目录');
            }
        }

        //查找上级Id,看是否是隐藏菜单.如果是隐藏菜单,子菜单也隐藏.
        if (isset($data['parent_id']) && $data['parent_id']) {
            if (static::where(['id' => $data['parent_id'], 'is_show' => 0])->exists()) {
                $data['is_show'] = 0;
            }
        }

        return static::updateOrCreate(['id' => $id], $data);
    }

    /**
     * 获取父节点的所有子节点
     * @param $data
     * @param $pid
     * @return array
     */
    public static function getChildrenByParentId($data, $pid)
    {
        $arr = [];
        foreach ($data as $v) {
            if ($v['parent_id'] == $pid) {
                $arr[] = $v['id'];
                $arr = array_merge($arr, static::getChildrenByParentId($data, $v['id']));
            }
        }
        return $arr;
    }

    /**
     * 删除菜单
     * @param $id
     * @return int
     * @throws \Exception
     */
    public function delMenu($id)
    {
        $check = static::where('parent_id', $id)->first();
        if (!empty($check)) {
            throw new \Exception('该目录下有子菜单，请先删除子菜单');
        }
        return static::destroy($id);
    }

    public static function findById(int $id, $guardName = null): MenuContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $menu = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (!$menu) {
            throw MenuDoesNotExist::withId($id);
        }

        return $menu;
    }
}
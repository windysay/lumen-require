<?php

namespace Yunhan\Rbac\Controllers;


use Auth;
use Illuminate\Http\Request;
use Yunhan\Rbac\Contracts\MenuContract;
use Yunhan\Rbac\Contracts\OutputDataFormatContract;
use Yunhan\Rbac\Models\Menu;

class MenuController extends BaseController
{
    /** @var Menu $menu */
    private $menu;

    public function __construct(MenuContract $menu, OutputDataFormatContract $output)
    {
        $this->output = $output;
        $this->menu = $menu;
    }

    /**
     * 获取目录列表
     */
    public function index(Request $request)
    {
        //是否显示隐藏菜单
        $showHidden = $request->input('showHidden', 0);
        return $this->output->success($this->menu->formatList($showHidden));
    }

    /**
     * 获取目录详情
     * @throws \Illuminate\Validation\ValidationException
     */
    public function show(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ], [
            'id.required' => '缺少id参数',
            'id.integer' => '参数不正确',
        ]);
        return $this->output->success($this->menu->find($request->input('id')));
    }

    /**
     * 新增目录
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'parent_id' => 'nullable|integer',
            'name' => 'required',
        ], [
            'parents_id.required' => '参数不正确',
            'name.required' => '请输入目录名',
        ]);

        try {
            return $this->output->success($this->menu->createOrUpdateMenu($request->all()));
        } catch (\Exception $exception) {
            return $this->output->error($exception->getMessage());
        }

    }

    /**
     * 修改菜单
     * @throws \Illuminate\Validation\ValidationException
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
            'parent_id' => 'nullable|integer',
            'name' => 'required',
        ], [
            'id.required' => '缺少id',
            'id.integer' => 'id参数不正确',
            'parent_id.required' => '请输入目录父类id',
            'parent_id.integer' => '父类id不正确',
            'name.required' => '请输入目录名',
            'path.required' => '请输入目录路径',
        ]);

        try {
            return $this->output->success(
                $this->menu->createOrUpdateMenu($request->all(), $request->input('id'))
            );
        } catch (\Exception $exception) {
            return $this->output->error($exception->getMessage());
        }

    }

    /**
     * 删除目录
     * @throws \Illuminate\Validation\ValidationException
     */
    public function destory(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ], [
            'id.required' => '缺少id',
            'id.integer' => 'id参数不正确',
        ]);

        try {
            return $this->output->success($this->menu->delMenu($request->input('id')));
        } catch (\Exception $exception) {
            return $this->output->error($exception->getMessage());
        }

    }

    /**
     * 通过权限获取用户对应菜单
     * @return mixed
     */
    public function menuList()
    {
        //获取用户
        $user = Auth::user();
        // @phan-suppress-next-line PhanTypeMismatchArgument
        return $this->output->success(Menu::findUserMenuByRole($user));
    }
}
<?php

namespace Yunhan\Rbac\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Yunhan\Rbac\Contracts\OutputDataFormatContract;
use Yunhan\Rbac\Models\Permission;

class PermissionController extends BaseController
{

    /** @var Permission $permission */
    private $permission;

    public function __construct(PermissionContract $permission, OutputDataFormatContract $output)
    {
        $this->output = $output;
        $this->permission = $permission;
    }

    /**
     * 列表
     * @return mixed
     */
    public function index()
    {
        $roles = $this->permission->list();

        return $this->output->success($roles);
    }

    /**
     * 权限详情
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function show(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ], [
            'id.required' => 'ID不能为空',
        ]);

        try {

            $permission = $this->permission->detail($request->input('id'));
            return $this->output->success($permission);
        } catch (PermissionDoesNotExist $exception) {
            return $this->output->error('权限不存在');
        }
    }

    /**
     * 创建权限
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'remark' => 'required|max:255',
            'name' => 'required|max:255',
            'menu_id' => 'required|integer',
            'method_id' => 'required|integer',
        ], [
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'remark.required' => '描述不能为空',
            'remark.max' => '描述不能超过255个字符',
            'method_id.required' => '方法不能为空',
            'menu_id.required' => '菜单不能为空',
        ]);

        try {
            $name = Permission::processName($request->input('method_id'), $request->input('name'));
            if (!$name) {
                return $this->output->error('方法参数错误');
            }
            $request->offsetSet('name', $name);
            $permission = $this->permission->add($request->all());
            return $this->output->success($permission);

        } catch (PermissionAlreadyExists $exception) {
            return $this->output->error('权限已经存在');
        } catch (\Exception $exception) {
            return $this->output->error($exception->getMessage());
        }

    }

    /**
     * 删除权限
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function destory(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ], [
            'id.required' => 'ID不能为空',
        ]);

        try {

            $this->permission->destory($request->input('id'));
            return $this->output->success([]);

        } catch (PermissionDoesNotExist $exception) {
            return $this->output->error('权限不存在');
        } catch (\Exception $exception) {
            return $this->output->error($exception->getMessage());
        }

    }

    /**
     * 编辑权限
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
            'remark' => 'required|max:255',
            'name' => 'required|max:255',
            'menu_id' => 'required|integer',
            'method_id' => 'required|integer',
        ], [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'remark.rquired' => '描述不能为空',
            'remark.max' => '描述不能超过255个字符',
            'method_id' => '方法不能为空',
        ]);

        try {

            $name = Permission::processName($request->input('method_id'), $request->input('name'));
            if (!$name) {
                return $this->output->error('方法参数错误');
            }
            $request->offsetSet('name', $name);
            if ($this->permission->edit($request->input('id'), $request->all())) {
                return $this->output->success([]);

            } else {
                return $this->output->error('修改失败');
            }

        } catch (PermissionDoesNotExist $exception) {
            return $this->output->error('权限不存在');
        }

    }

    /**
     * 获取所有权限
     * @return Permission[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermission()
    {
        return $this->output->success($this->permission->list());
    }

    /**
     * 获取请求方法
     * @return array
     */
    public function getRequestMethodList()
    {
        $methods = Permission::getRequestMethondList();
        $data = [];
        foreach ($methods as $k => $method) {
            $data[] = [
                'name' => $method,
                'value' => $k,
            ];
        }

        return $this->output->success($data);
    }

}
<?php

namespace Yunhan\Rbac\Controllers;


use Illuminate\Http\Request;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Yunhan\Rbac\Contracts\OutputDataFormatContract;
use Yunhan\Rbac\Models\Role;

class RoleController extends BaseController
{

    /** @var Role $role */
    private $role;

    public function __construct(RoleContract $role, OutputDataFormatContract $output)
    {
        $this->output = $output;
        $this->role = $role;
    }

    /**
     * 角色列表
     * @return mixed
     */
    public function index()
    {
        $roles = $this->role->list();

        return $this->output->success($roles);
    }

    /**
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

            $role = $this->role->findWithPermissions($request->input('id'));
            return $this->output->success($role);
        } catch (RoleDoesNotExist $exception) {
            return $this->output->error('角色不存在');
        }

    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'permission_ids' => 'required',
        ], [
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'permission_ids.required' => '权限不能为空',
        ]);

        try {

            $resul = $this->role->add($request->all());
            return $this->output->success($resul);
        } catch (RoleAlreadyExists $exception) {
            return $this->output->error('权限已经存在');
        }

    }

    /**
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
            //这里要使用Model的delete方法,触发删除事件清除缓存.同时这里也会把关联的中间表删掉
            $this->role->destory($request->input('id'));
            return $this->output->success([]);
        } catch (RoleDoesNotExist $exception) {
            return $this->output->error('角色不存在');
        }

    }

    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
            'name' => 'required|max:255',
            'permission_ids' => 'required',
        ], [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'permission_ids.required' => '权限不能为空',
        ]);

        try {

            $this->role->edit($request->all());
            return $this->output->success([]);
        } catch (RoleDoesNotExist $exception) {
            return $this->output->error('角色不存在');
        }

    }

    /**
     * 获取所有角色
     * @return Role[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllRole()
    {
        return $this->output->success(Role::all());
    }

    /**
     * 根据角色获取权限
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getPermissionByRole(Request $request)
    {
        $this->validate($request, [
            'role_ids' => 'required'
        ], [
            'role_ids.required' => '角色Id不能为空',
        ]);

        try {
            $roleIds = explode(',', $request->input('role_ids'));
            $permissions = $this->role->findWithPermissionByRole($roleIds);
            return $this->output->success($permissions);
        } catch (RoleDoesNotExist $exception) {
            return $this->output->error('角色不存在');
        }
    }
}
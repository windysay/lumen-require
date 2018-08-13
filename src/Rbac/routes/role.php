<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group(['prefix' => 'role'], function (Router $router) {
    //角色列表
    $router->get('index', 'RoleController@index');
    //角色详情
    $router->get('show', 'RoleController@show');
    //创建角色
    $router->post('create', 'RoleController@create');
    //编辑角色
    $router->post('edit', 'RoleController@edit');
    //删除角色
    $router->post('delete', 'RoleController@destory');
    //获取所有角色
    $router->get('allrole', 'RoleController@getAllrole');
    //通过角色获取权限
    $router->get('getPermissionByRole', 'RoleController@getPermissionByRole');
});
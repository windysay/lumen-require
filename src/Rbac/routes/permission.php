<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group(['prefix' => 'permission'], function (Router $router) {
    //权限列表
    $router->get('index', 'PermissionController@index');
    //权限详情
    $router->get('show', 'PermissionController@show');
    //创建权限
    $router->post('create', 'PermissionController@create');
    //编辑权限
    $router->post('edit', 'PermissionController@edit');
    //删除权限
    $router->post('delete', 'PermissionController@destory');
    //获取所有权限
    $router->get('allPermission', 'PermissionController@getAllPermission');
    //获取请求方法
    $router->get('requestMethodList', 'PermissionController@getRequestMethodList');
});
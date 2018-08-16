<?php

use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group(['prefix' => 'menu'], function (Router $router) {
    //菜单列表
    $router->get('index', 'MenuController@index');
    //菜单详情
    $router->get('show', 'MenuController@show');
    //创建菜单
    $router->post('create', 'MenuController@create');
    //编辑菜单
    $router->post('edit', 'MenuController@edit');
    //删除菜单
    $router->post('delete', 'MenuController@destory');
});
<?php

namespace Yunhan\Rbac\Requests;

use Faker\Factory;
use Faker\Generator;
use Yunhan\Rbac\Tests\App\Auth\User;
use Yunhan\Rbac\Tests\BaseTestCase;

class MenuTest extends BaseTestCase
{
    /** @var Generator $faker */
    protected $faker;
    protected $table;
    protected $connection;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create('zh_CN');
        $this->table = config('permission.table_names.menus');
        $this->connection = config('permission.connection');
    }

    public function testIndex()
    {
        $this->get('menu/index')
            ->assertResponseStatus(200);
    }

    public function testShow()
    {
        $menu = $this->generateMenu();

        $this->get('menu/show?id=' . $menu['id'])
            ->assertResponseStatus(200);
    }

    public function generateMenu()
    {
        $menu = $this->call('POST', 'menu/create', $this->getParameters())
            ->getOriginalContent();

        return $menu['data'];
    }

    public function getParameters()
    {
        return [
            'parent_id' => 0,
            'name' => $this->faker->name,
            'path' => $this->faker->name,
        ];
    }

    public function testCreate()
    {

        $this->post('menu/create', $this->getParameters())
            ->assertResponseStatus(200);

        $response = $this->response->getOriginalContent();
        $this->assertArrayHasKey('id', $response['data']);
    }

    public function testEdit()
    {
        $menu = $this->generateMenu();

        $name = 'Hello World';

        $params = [
            'id' => $menu['id'],
            'parent_id' => $menu['parent_id'],
            'name' => $name,
        ];

        $this->post('menu/edit', $params);
        $this->seeInDatabase($this->table, ['name' => $name], $this->connection);

    }

    public function testDestory()
    {
        $menu = $this->generateMenu();

        $this->post('menu/delete', ['id' => $menu['id']]);

        $this->notSeeInDatabase($this->table, ['id' => $menu['id']], $this->connection);
    }

    public function testMenuList()
    {
        //模拟登陆用户
        $user = User::find(1);
        $this->be($user, static::DEFAULT_GUARD);

        $this->get('menu/menuList')
            ->assertResponseStatus(200);
    }
}
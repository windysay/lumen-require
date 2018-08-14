<?php

namespace Yunhan\Rbac\Requests;

use Faker\Factory;
use Faker\Generator;
use Yunhan\Rbac\Models\Menu;
use Yunhan\Rbac\Models\Permission;
use Yunhan\Rbac\Tests\BaseTestCase;

class PermissionTest extends BaseTestCase
{

    /** @var Generator $faker */
    protected $faker;
    protected $table;
    protected $connection;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create('zh_CN');
        $this->table = config('permission.table_names.permissions');
        $this->connection = config('permission.connection');
    }

    public function testIndex()
    {
        $this->get('permission/index')
            ->assertResponseStatus(200);
    }

    public function testShow()
    {
        $permission = $this->generatePermission();

        $this->get('permission/show?id=' . $permission['id'])
            ->assertResponseStatus(200);

    }

    public function generatePermission()
    {
        $permission = $this->call('POST', 'permission/create', $this->getParameters())
            ->getOriginalContent();

        return $permission['data'];
    }

    public function getParameters()
    {
        $menuId = Menu::withoutGlobalScopes()
            ->where('parent_id', '>', 0)
            ->where(['guard_name' => 'admin'])
            ->value('id');
        return [
            'remark' => $this->faker->name,
            'name' => $this->faker->name,
            'menu_id' => $menuId,
            'method_id' => 1,
        ];
    }

    public function testCreate()
    {

        $this->post('permission/create', $this->getParameters())
            ->assertResponseStatus(200);

        $response = $this->response->getOriginalContent();
        $this->assertArrayHasKey('id', $response['data']);
    }

    public function testEdit()
    {
        $permission = $this->generatePermission();

        $name = 'Hello World';

        $params = [
            'id' => $permission['id'],
            'name' => $name,
            'menu_id' => $permission['menu_id'],
            'method_id' => 2,
            'remark' => $name,
        ];

        $this->post('permission/edit', $params)
            ->assertResponseStatus(200);

        $this->seeInDatabase($this->table, ['name' => Permission::processName(2, $name)], $this->connection);

    }

    public function testDestory()
    {
        $permission = $this->generatePermission();

        $this->post('permission/delete', ['id' => $permission['id']])
            ->assertResponseStatus(200);

        $this->notSeeInDatabase($this->table, ['id' => $permission['id']], $this->connection);
    }

    public function testGetAllPermission()
    {
        $this->get('permission/allPermission')
            ->assertResponseStatus(200);
    }

    public function testGetRequestMethodList()
    {
        $this->get('permission/requestMethodList')
            ->assertResponseStatus(200);
    }

}
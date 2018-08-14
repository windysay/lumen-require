<?php

namespace Yunhan\Rbac\Requests;

use Faker\Factory;
use Faker\Generator;
use Yunhan\Rbac\Models\Menu;
use Yunhan\Rbac\Tests\BaseTestCase;

class RoleTest extends BaseTestCase
{

    /** @var Generator $faker */
    protected $faker;
    protected $table;
    protected $connection;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create('zh_CN');
        $this->table = config('permission.table_names.roles');
        $this->connection = config('permission.connection');
    }

    public function testIndex()
    {
        $this->get('role/index')
            ->assertResponseStatus(200);
    }

    public function testShow()
    {
        $role = $this->generateRole();

        $this->get('role/show?id=' . $role['id'])
            ->assertResponseStatus(200);
    }

    public function generateRole()
    {
        $role = $this->call('POST', 'role/create', $this->getParameters())
            ->getOriginalContent();

        return $role['data'];
    }

    public function getParameters()
    {
        $permission = $this->generatePermission();

        return [
            'name' => $this->faker->name,
            'permission_ids' => $permission['id'],
        ];
    }

    public function generatePermission()
    {
        $menuId = Menu::withoutGlobalScopes()
            ->where('parent_id', '>', 0)
            ->where(['guard_name' => 'admin'])
            ->value('id');
        $params = [
            'remark' => $this->faker->name,
            'name' => $this->faker->name,
            'menu_id' => $menuId,
            'method_id' => 1,
        ];
        $permission = $this->call('POST', 'permission/create', $params)
            ->getOriginalContent();

        return $permission['data'];
    }

    public function testCreate()
    {

        $this->post('role/create', $this->getParameters())
            ->assertResponseStatus(200);

        $response = $this->response->getOriginalContent();
        $this->assertArrayHasKey('id', $response['data']);
    }

    public function testEdit()
    {
        $role = $this->generateRole();
        $permission = $this->generatePermission();

        $name = 'Hello World';

        $params = [
            'id' => $role['id'],
            'name' => $name,
            'method_id' => 2,
            'permission_ids' => $permission['id'],
        ];

        $this->post('role/edit', $params)
            ->assertResponseStatus(200);

        $this->seeInDatabase($this->table, ['name' => $name], $this->connection);

    }

    public function testDestory()
    {
        $role = $this->generateRole();

        $this->post('role/delete', ['id' => $role['id']]);

        $this->notSeeInDatabase($this->table, ['id' => $role['id']], $this->connection);
    }

    public function testGetAllRole()
    {
        $this->get('role/allrole')
            ->assertResponseStatus(200);
    }

    public function testGetPermissionByRole()
    {

        $permission = $this->generatePermission();
        //生成角色
        $data = $this->call('POST', 'role/create',
            ['name' => $this->faker->name, 'permission_ids' => $permission['id']])
            ->getOriginalContent();

        $this->get('role/getPermissionByRole?role_ids=' . $data['data']['id'])
            ->assertResponseStatus(200);
    }

}
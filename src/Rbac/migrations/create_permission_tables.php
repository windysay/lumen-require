<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');

        $schema = Schema::connection(config('permission.connection'));

        $schema->create($tableNames['permissions'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('remark')->default('')->comment('描述');
            $table->string('name')->default('')->comment('请求方式+路由');
            $table->mediumInteger('menu_id')->default(0)->comment('菜单ID');
            $table->string('guard_name')->comment('守卫,区分模块.比如:前端(web),后端(admin)');
            $table->timestamps();
        });

        $schema->create($tableNames['roles'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('角色名称');
            $table->string('guard_name')->comment('守卫,区分模块.比如:前端(web),后端(admin)');
            $table->timestamps();
        });

        $schema->create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedInteger('permission_id');
            $table->morphs('model');

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(['permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary');
        });

        $schema->create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedInteger('role_id');
            $table->morphs('model');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        $schema->create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);

            app('cache')->forget('spatie.permission.cache');
        });

        $schema->create($tableNames['role_has_menus'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedInteger('menu_id');
            $table->unsignedInteger('role_id');

            $table->foreign('menu_id')
                ->references('id')
                ->on($tableNames['menus'])
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['menu_id', 'role_id']);

            app('cache')->forget('spatie.permission.cache');
        });

        $schema->create($tableNames['menus'], function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->default(0)->comment('父Id');
            $table->string('name')->default('菜单名称');
            $table->tinyInteger('is_show')->default(1)->comment('是否显示菜单 1显示 0不显示 默认显示');
            $table->string('guard_name')->comment('守卫,区分模块.比如:前端(web),后端(admin)');
            $table->string('path')->default('')->comment('前端扩展用,可以写菜单对应的前端的路由');
            $table->string('icon')->default('')->comment('图标');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        $schema = Schema::connection(config('permission.connection'));

        $schema->drop($tableNames['role_has_permissions']);
        $schema->drop($tableNames['model_has_roles']);
        $schema->drop($tableNames['model_has_permissions']);
        $schema->drop($tableNames['roles']);
        $schema->drop($tableNames['permissions']);
        $schema->drop($tableNames['menus']);
        $schema->drop($tableNames['role_has_menus']);
    }
}

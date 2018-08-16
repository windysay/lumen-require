<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('JAuth.table_names');

        Schema::create($tableNames['ticket'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid');
            $table->string('guard', '32');
            $table->string('token', 128);
            $table->string('status');
            $table->string('ip');
            $table->string('expiration');
            $table->timestamps();
            $table->unique(['token', 'guard']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = config('JAuth.table_names.ticket');
        Schema::dropIfExists($tableName);
    }
}

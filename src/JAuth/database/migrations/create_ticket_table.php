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
            $table->string('token', 128)->unique()->index();
            $table->string('status');
            $table->string('ip');
            $table->string('expiration');
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
        $tableName = config('JAuth.table_names.ticket');
        Schema::dropIfExists($tableName);
    }
}

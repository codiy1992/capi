<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('group', 8)->default('')->comment('分组');
            $table->string('name', 32)->default('')->comment('节点名称');
            $table->string('ipv4', 32)->default('')->comment('节点地址');
            $table->tinyInteger('status')->default(1)->comment('状态 0:未启用 1:已启用');
            $table->unique('ipv4');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servers');
    }
}

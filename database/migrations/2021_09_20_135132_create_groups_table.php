<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->default('')->comment('分组名称');
            $table->string('type', 32)->default('http')->comment('类型');
            $table->string('url', 255)->default('')->comment('订阅地址');
            $table->unsignedInteger('interval')->default(0)->comment('更新间隔');
            $table->string('path', 255)->default('')->comment('存储位置');
            $table->json('health_check')->comment('健康检查');
            $table->string('remark', 128)->default('')->comment('备注');
            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
            $table->unsignedInteger('updated_at')->default(0)->comment('更新时间');
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
}

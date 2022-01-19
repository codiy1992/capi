<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('config', 32)->default('')->comment('访问配置');
            $table->string('ipv4', 32)->default('')->comment('IP地址');
            $table->string('uri', 32)->default('')->comment('访问路径');
            $table->string('agent', 255)->default('')->comment('客户端代理');
            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_logs');
    }
}

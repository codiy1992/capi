<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnycastLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anycast_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider',32)->default('')->comment('提供商');
            $table->string('ipv4', 32)->default('')->comment('IP地址');
            $table->string('down', 32)->default('')->comment('下载速度');
            $table->string('icmp', 32)->default('')->comment('icmp时间');
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
        Schema::dropIfExists('anycast_logs');
    }
}

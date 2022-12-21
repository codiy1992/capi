<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProtocolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('protocols', function (Blueprint $table) {
            $table->id();
            $table->string('name', 8)->default('')->comment('代理协议');
            $table->string('transport', 32)->default('')->comment('传输流协议');
            $table->tinyInteger('status')->default(0)->comment('状态 0:未启用 1:已启用');
            $table->unsignedInteger('port')->default(0)->comment('端口');
            $table->tinyInteger('tls')->default(0)->comment('TLS 0:否 1:是');
            $table->string('cipher', 32)->default('')->comment('密码学套件');
            $table->unsignedInteger('alterId')->default(0)->comment('alterId');
            $table->string('uuid', 48)->default('')->comment('唯一ID');
            $table->string('password', 64)->default('')->comment('密码');
            $table->string('psk', 64)->default('')->comment('snell psk');
            $table->json('extra')->comment('额外配置');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('protocols');
    }
}

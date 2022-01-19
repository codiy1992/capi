<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProxiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->default('')->comment('节点名称');
            $table->string('type', 16)->default('')->comment('节点类型');
            $table->string('server', 64)->default('')->comment('节点地址');
            $table->unsignedInteger('port')->default(0)->comment('端口');
            $table->tinyInteger('status')->default(0)->comment('状态 -1:不可用 0:未启用 1:正常');
            $table->string('groups', 64)->default('')->comment('分组');
            $table->tinyInteger('modify')->default(0)->comment('是否有改动 0:否 1:是');
            $table->string('source', 64)->default('')->comment('来源');
            $table->string('cipher', 32)->default('')->comment('密码学套件');
            $table->unsignedInteger('alterId')->default(0)->comment('alterId');
            $table->string('uuid', 48)->default('')->comment('唯一ID');
            $table->string('password', 64)->default('')->comment('密码');
            $table->string('psk', 64)->default('')->comment('snell psk');
            $table->json('extra')->comment('额外配置');
            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
            $table->unsignedInteger('updated_at')->default(0)->comment('更新时间');
            $table->unique(['server', 'port']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proxies');
    }
}

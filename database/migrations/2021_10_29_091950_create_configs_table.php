<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->default('')->comment('配置名称');
            $table->tinyInteger('dns')->default(1)->comment('是否开启内置DNS');
            $table->string('groups', 255)->default('')->comment('允许的分组名单');
            $table->tinyInteger('interval')->default(0)->comment('provider 更新频率 单位: s');
            $table->tinyInteger('shuffle')->default(0)->comment('proxies 是否乱序返回 0:否 1:是');
            $table->tinyInteger('single')->default(0)->comment('proxies 是否返回单一节点 0:否 1:是');
            $table->tinyInteger('debug')->default(0)->comment('协议调试 0:否 1:是');
            $table->json('exclude')->comment('过滤规则');
            $table->json('extra')->comment('额外规则');
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
        Schema::dropIfExists('configs');
    }
}

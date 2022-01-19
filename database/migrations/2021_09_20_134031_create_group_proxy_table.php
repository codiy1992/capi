<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupProxyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_proxy', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('group_id')->default(0)->comment('分组ID');
            $table->unsignedInteger('proxy_id')->default(0)->comment('代理ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_proxy');
    }
}

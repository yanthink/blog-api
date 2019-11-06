<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('wechat_openid')->nullable()->unique()->comment('小程序OPENID');
            $table->string('password');
            $table->rememberToken();
            $table->string('avatar');
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->string('bio')->comment('座右铭');
            $table->json('settings')->nullable()->comment('个人设置');
            $table->json('extends')->nullable()->comment('扩展数据');
            $table->json('cache')->nullable()->comment('数据缓存');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}

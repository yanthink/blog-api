<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replys', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id'); // 回复用户
            $table->string('content', 1024); // 回复内容
            $table->morphs('target');
            $table->unsignedInteger('parent_id')->default(0); // 回复父id
            $table->unsignedInteger('like_count')->default(0); // 点赞次数
            $table->timestamp('created_at', 0)->nullable();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('replys');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersOnlineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_online', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable();
            $table->string('ip');
            $table->unsignedSmallInteger('stack_level');
            $table->timestamps();
            $table->primary('user_id');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_online');
    }
}

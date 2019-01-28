<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->comment('用户名称');
            $table->string('email', 100)->comment('用户邮箱');
            $table->string('password', 255)->comment('用户密码');
            $table->string('icon', 255)->comment('用户头像')->default('/uploads/icon/1.jpg');;
            $table->enum('admin', ['no', 'yes'])->default('no')->comment('是否是管理员 no否 yes是');
            $table->enum('forbidden', ['no', 'yes'])->default('no')->comment('是否禁用');
            $table->timestamps();
            $table->timestamp('last_login_time')->comment('最后登录时间');
            $table->unique('name');
            $table->unique('email');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

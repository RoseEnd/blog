<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('comment_id')->comment('关联的评论id');
            $table->unsignedInteger('article_id')->comment('关联的文章id');
            $table->text('content')->comment('回复的内容');
            $table->unsignedInteger('user_id')->comment('关联的回复人id');
            $table->string('user_name', 255)->comment('关联的回复人name');
            $table->timestamps();
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
        Schema::dropIfExists('replies');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id')->comment('自增');
            $table->string('title', 255)->comment('文章标题');
            $table->text('content')->comment('文章内容');
            $table->text('render')->comment('渲染后的html');
            $table->string('image', 255)->comment('文章缩略图');
            $table->enum('pull_top', ['no', 'yes'])->default('yes')->comment('是否置顶 0否1是');
            $table->enum('article_support', ['no', 'yes'])->default('yes')->comment('博主推荐yes推荐no不推荐');
            $table->enum('status', ['public', 'private'])->default('public')->comment('文章模式public公开private私有');
            $table->unsignedBigInteger('click_number')->default('0')->comment('文章点击数');
            $table->string('key_words',255)->comment('关键字seo优化');
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
        Schema::dropIfExists('articles');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
/*广告表*/
class CreateAdvertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adverts', function (Blueprint $table) {
            $table->increments('id');
            $table->text('code')->comment('广告代码');
            $table->string('description', 255)->comment('广告描述');
            $table->enum('position', ['top', 'middle', 'bottom', 'banner'])
                ->comment('摆放位置 banner 横条摆放 top 右侧上部  middle 右侧中部 bottom 右侧底部');
            $table->enum('display', ['yes', 'no'])->comment('是否显示');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adverts');
    }
}

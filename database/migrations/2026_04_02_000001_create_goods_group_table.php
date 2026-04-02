<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsGroupTable extends Migration
{
    public function up()
    {
        Schema::create('goods_group', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gp_name', 200)->comment('分类名称');
            $table->boolean('is_open')->default(1)->comment('是否启用，1是 0否');
            $table->integer('ord')->default(1)->comment('排序权重 越大越靠前');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('goods_group');
    }
}

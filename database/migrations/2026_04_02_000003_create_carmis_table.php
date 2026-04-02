<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarmisTable extends Migration
{
    public function up()
    {
        Schema::create('carmis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('goods_id')->comment('所属商品');
            $table->boolean('status')->default(1)->comment('状态 1未售出 2已售出');
            $table->boolean('is_loop')->default(0)->comment('循环卡密 1是 0否');
            $table->text('carmi')->comment('卡密');
            $table->timestamps();
            $table->softDeletes();

            $table->index('goods_id', 'idx_goods_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carmis');
    }
}

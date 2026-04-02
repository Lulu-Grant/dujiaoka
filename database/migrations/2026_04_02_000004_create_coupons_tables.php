<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTables extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('discount', 10, 2)->default('0.00')->comment('优惠金额');
            $table->boolean('is_use')->default(1)->comment('是否已经使用 1未使用 2已使用');
            $table->boolean('is_open')->default(1)->comment('是否启用 1是 0否');
            $table->string('coupon', 150)->comment('优惠码');
            $table->integer('ret')->default(0)->comment('剩余使用次数');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('coupon', 'idx_coupon');
        });

        Schema::create('coupons_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('goods_id')->comment('商品id');
            $table->integer('coupons_id')->comment('优惠码id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons_goods');
        Schema::dropIfExists('coupons');
    }
}

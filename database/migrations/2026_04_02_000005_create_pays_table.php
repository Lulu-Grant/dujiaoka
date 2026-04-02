<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaysTable extends Migration
{
    public function up()
    {
        Schema::create('pays', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pay_name', 200)->comment('支付名称');
            $table->string('pay_check', 50)->comment('支付标识');
            $table->boolean('pay_method')->comment('支付方式 1跳转 2扫码');
            $table->boolean('pay_client')->default(1)->comment('支付场景：1电脑pc 2手机 3全部');
            $table->string('merchant_id', 200)->nullable()->comment('商户 ID');
            $table->text('merchant_key')->nullable()->comment('商户 KEY');
            $table->text('merchant_pem')->comment('商户密钥');
            $table->string('pay_handleroute', 200)->comment('支付处理路由');
            $table->boolean('is_open')->default(1)->comment('是否启用 1是 0否');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('pay_check', 'idx_pay_check');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pays');
    }
}

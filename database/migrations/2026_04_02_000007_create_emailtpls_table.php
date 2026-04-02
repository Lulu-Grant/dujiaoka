<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailtplsTable extends Migration
{
    public function up()
    {
        Schema::create('emailtpls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tpl_name', 150)->comment('邮件标题');
            $table->text('tpl_content')->comment('邮件内容');
            $table->string('tpl_token', 50)->comment('邮件标识');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('tpl_token', 'idx_emailtpl_token');
        });
    }

    public function down()
    {
        Schema::dropIfExists('emailtpls');
    }
}

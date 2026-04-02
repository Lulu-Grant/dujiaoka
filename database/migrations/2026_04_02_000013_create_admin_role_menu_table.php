<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRoleMenuTable extends Migration
{
    public function up()
    {
        Schema::create('admin_role_menu', function (Blueprint $table) {
            $table->bigInteger('role_id');
            $table->bigInteger('menu_id');
            $table->timestamps();

            $table->unique(['role_id', 'menu_id'], 'admin_role_menu_role_id_menu_id_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_role_menu');
    }
}

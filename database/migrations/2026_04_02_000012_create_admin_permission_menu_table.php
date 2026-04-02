<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPermissionMenuTable extends Migration
{
    public function up()
    {
        Schema::create('admin_permission_menu', function (Blueprint $table) {
            $table->bigInteger('permission_id');
            $table->bigInteger('menu_id');
            $table->timestamps();

            $table->unique(['permission_id', 'menu_id'], 'admin_permission_menu_permission_id_menu_id_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_permission_menu');
    }
}

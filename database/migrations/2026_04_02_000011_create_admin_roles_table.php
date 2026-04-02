<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRolesTable extends Migration
{
    public function up()
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->timestamps();

            $table->unique('slug', 'admin_roles_slug_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_roles');
    }
}

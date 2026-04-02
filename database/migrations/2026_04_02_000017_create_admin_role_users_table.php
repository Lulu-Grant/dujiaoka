<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRoleUsersTable extends Migration
{
    public function up()
    {
        Schema::create('admin_role_users', function (Blueprint $table) {
            $table->bigInteger('role_id');
            $table->bigInteger('user_id');
            $table->timestamps();

            $table->unique(['role_id', 'user_id'], 'admin_role_users_role_id_user_id_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_role_users');
    }
}

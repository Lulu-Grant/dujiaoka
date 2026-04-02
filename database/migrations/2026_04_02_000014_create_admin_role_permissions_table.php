<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRolePermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('admin_role_permissions', function (Blueprint $table) {
            $table->bigInteger('role_id');
            $table->bigInteger('permission_id');
            $table->timestamps();

            $table->unique(['role_id', 'permission_id'], 'admin_role_permissions_role_id_permission_id_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_role_permissions');
    }
}

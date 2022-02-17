<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomDataToRoleAndPermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->json('custom_data')->after('description')->nullable();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->json('custom_data')->after('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('custom_data');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->dropColumn('custom_data');
        });
    }
}

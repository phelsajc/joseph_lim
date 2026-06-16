<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMedcertControlNoToAppointments extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('appointments', 'medcert_control_no')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('medcert_control_no', 20)->nullable()->unique()->after('medcert_remarks');
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique(['medcert_control_no']);
            $table->dropColumn('medcert_control_no');
        });
    }
}

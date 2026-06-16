<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeAppointmentVitalsAppointmentIdNullable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE appointment_vitals MODIFY appointment_id BIGINT UNSIGNED NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE appointment_vitals MODIFY appointment_id BIGINT UNSIGNED NOT NULL');
    }
}

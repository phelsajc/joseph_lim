<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentVitalsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('appointment_vitals')) {
            return;
        }

        Schema::create('appointment_vitals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('appointment_id');
            $table->string('patientid', 50);
            $table->dateTime('recorded_at');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->string('vit_sys', 50)->nullable();
            $table->string('vit_dia', 50)->nullable();
            $table->string('weight', 50)->nullable();
            $table->string('height', 50)->nullable();
            $table->string('bmi', 50)->nullable();
            $table->string('vit_temp', 50)->nullable();
            $table->string('vit_cr', 50)->nullable();
            $table->string('vit_rr', 50)->nullable();
            $table->string('o2_stat', 50)->nullable();
            $table->index(['appointment_id', 'recorded_at'], 'appt_vitals_appt_recorded_idx');
            $table->index(['patientid', 'recorded_at'], 'appt_vitals_patient_recorded_idx');
        });

        $appointments = DB::table('appointments')
            ->where('is_cancel', 0)
            ->whereNotNull('patientid')
            ->where('patientid', '!=', '')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('vit_sys')->where('vit_sys', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('vit_dia')->where('vit_dia', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('weight')->where('weight', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('height')->where('height', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('bmi')->where('bmi', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('vit_temp')->where('vit_temp', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('vit_cr')->where('vit_cr', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('vit_rr')->where('vit_rr', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('o2_stat')->where('o2_stat', '!=', '');
                });
            })
            ->get();

        foreach ($appointments as $apt) {
            $recordedAt = $apt->updated_dt ?: ($apt->created_dt ?: ($apt->appointment_dt . ' 00:00:00'));

            DB::table('appointment_vitals')->insert([
                'appointment_id' => $apt->id,
                'patientid' => $apt->patientid,
                'recorded_at' => $recordedAt,
                'recorded_by' => $apt->updated_by ?: $apt->created_by,
                'vit_sys' => $apt->vit_sys,
                'vit_dia' => $apt->vit_dia,
                'weight' => $apt->weight,
                'height' => $apt->height,
                'bmi' => $apt->bmi,
                'vit_temp' => $apt->vit_temp,
                'vit_cr' => $apt->vit_cr,
                'vit_rr' => $apt->vit_rr,
                'o2_stat' => $apt->o2_stat,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('appointment_vitals');
    }
}

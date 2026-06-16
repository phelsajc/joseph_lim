<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePrescriptionGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('prescription_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('appointment_id');
            $table->string('title', 255)->default('Prescription 1');
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('created_dt')->nullable();
            $table->index(['appointment_id', 'sort_order'], 'rx_groups_appt_sort_idx');
        });

        if (Schema::hasTable('rx') && !Schema::hasColumn('rx', 'prescription_group_id')) {
            Schema::table('rx', function (Blueprint $table) {
                $table->unsignedBigInteger('prescription_group_id')->nullable()->after('appointment_id');
                $table->index('prescription_group_id', 'rx_prescription_group_id_idx');
            });
        }

        $appointmentIds = DB::table('rx')
            ->select('appointment_id')
            ->whereNotNull('appointment_id')
            ->distinct()
            ->pluck('appointment_id');

        foreach ($appointmentIds as $appointmentId) {
            $groupId = DB::table('prescription_groups')->insertGetId([
                'appointment_id' => $appointmentId,
                'title' => 'Prescription 1',
                'sort_order' => 0,
                'created_dt' => now(),
            ]);

            DB::table('rx')
                ->where('appointment_id', $appointmentId)
                ->whereNull('prescription_group_id')
                ->update(['prescription_group_id' => $groupId]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('rx') && Schema::hasColumn('rx', 'prescription_group_id')) {
            Schema::table('rx', function (Blueprint $table) {
                $table->dropIndex('rx_prescription_group_id_idx');
                $table->dropColumn('prescription_group_id');
            });
        }

        Schema::dropIfExists('prescription_groups');
    }
}

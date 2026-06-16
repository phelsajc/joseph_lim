<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDiagnosticGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('diagnostic_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('appointment_id');
            $table->string('title', 255)->default('Diagnostics 1');
            $table->text('lab_remarks')->nullable();
            $table->text('findings')->nullable();
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('created_dt')->nullable();
            $table->index(['appointment_id', 'sort_order'], 'dx_groups_appt_sort_idx');
        });

        if (Schema::hasTable('ancillary') && !Schema::hasColumn('ancillary', 'diagnostic_group_id')) {
            Schema::table('ancillary', function (Blueprint $table) {
                $table->unsignedBigInteger('diagnostic_group_id')->nullable()->after('appointment_id');
                $table->index('diagnostic_group_id', 'ancillary_diagnostic_group_id_idx');
            });
        }

        if (!Schema::hasTable('ancillary')) {
            return;
        }

        $appointmentIds = DB::table('ancillary')
            ->select('appointment_id')
            ->whereNotNull('appointment_id')
            ->distinct()
            ->pluck('appointment_id');

        foreach ($appointmentIds as $appointmentId) {
            $labRemarks = null;
            if (Schema::hasTable('appointments')) {
                $labRemarks = DB::table('appointments')
                    ->where('id', $appointmentId)
                    ->value('lab_remarks');
            }

            $groupId = DB::table('diagnostic_groups')->insertGetId([
                'appointment_id' => $appointmentId,
                'title' => 'Diagnostics 1',
                'lab_remarks' => $labRemarks,
                'sort_order' => 0,
                'created_dt' => now(),
            ]);

            DB::table('ancillary')
                ->where('appointment_id', $appointmentId)
                ->whereNull('diagnostic_group_id')
                ->update(['diagnostic_group_id' => $groupId]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('ancillary') && Schema::hasColumn('ancillary', 'diagnostic_group_id')) {
            Schema::table('ancillary', function (Blueprint $table) {
                $table->dropIndex('ancillary_diagnostic_group_id_idx');
                $table->dropColumn('diagnostic_group_id');
            });
        }

        Schema::dropIfExists('diagnostic_groups');
    }
}

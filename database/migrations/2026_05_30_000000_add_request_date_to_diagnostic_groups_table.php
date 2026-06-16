<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestDateToDiagnosticGroupsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('diagnostic_groups')) {
            return;
        }

        if (!Schema::hasColumn('diagnostic_groups', 'request_date')) {
            Schema::table('diagnostic_groups', function (Blueprint $table) {
                $table->date('request_date')->nullable()->after('lab_remarks');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('diagnostic_groups') && Schema::hasColumn('diagnostic_groups', 'request_date')) {
            Schema::table('diagnostic_groups', function (Blueprint $table) {
                $table->dropColumn('request_date');
            });
        }
    }
}

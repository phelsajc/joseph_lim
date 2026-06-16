<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToAncillaryTable extends Migration
{
    public function up()
    {
        Schema::table('ancillary', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('appointment_id');
        });

        DB::table('ancillary')->update(['sort_order' => DB::raw('id')]);
    }

    public function down()
    {
        Schema::table('ancillary', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}

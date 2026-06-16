<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToRxTable extends Migration
{
    public function up()
    {
        Schema::table('rx', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('appointment_id');
        });

        DB::table('rx')->update(['sort_order' => DB::raw('rx_id')]);
    }

    public function down()
    {
        Schema::table('rx', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}

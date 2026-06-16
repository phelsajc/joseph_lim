<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultDosageColumnsToMedicinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medicines', function (Blueprint $table) {
            if (!Schema::hasColumn('medicines', 'default_qty')) {
                $table->string('default_qty', 64)->nullable();
            }
            if (!Schema::hasColumn('medicines', 'default_bf_b')) {
                $table->string('default_bf_b', 64)->nullable();
            }
            if (!Schema::hasColumn('medicines', 'default_bf_a')) {
                $table->string('default_bf_a', 64)->nullable();
            }
            if (!Schema::hasColumn('medicines', 'default_l_b')) {
                $table->string('default_l_b', 64)->nullable();
            }
            if (!Schema::hasColumn('medicines', 'default_l_a')) {
                $table->string('default_l_a', 64)->nullable();
            }
            if (!Schema::hasColumn('medicines', 'default_s_b')) {
                $table->string('default_s_b', 64)->nullable();
            }
            if (!Schema::hasColumn('medicines', 'default_s_a')) {
                $table->string('default_s_a', 64)->nullable();
            }
            if (!Schema::hasColumn('medicines', 'default_bt')) {
                $table->string('default_bt', 64)->nullable();
            }
            if (!Schema::hasColumn('medicines', 'default_remarks')) {
                $table->text('default_remarks')->nullable();
            }
            if (!Schema::hasColumn('medicines', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medicines', function (Blueprint $table) {
            $columns = [
                'default_qty',
                'default_bf_b',
                'default_bf_a',
                'default_l_b',
                'default_l_a',
                'default_s_b',
                'default_s_a',
                'default_bt',
                'default_remarks',
                'created_at',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('medicines', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}

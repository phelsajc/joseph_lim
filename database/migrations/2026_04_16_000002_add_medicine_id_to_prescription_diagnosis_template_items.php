<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMedicineIdToPrescriptionDiagnosisTemplateItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('prescription_diagnosis_template_items', 'medicine_id')) {
            Schema::table('prescription_diagnosis_template_items', function (Blueprint $table) {
                /* No FK: legacy medicines.id may not match bigint unsigned for FK compatibility */
                $table->unsignedInteger('medicine_id')->nullable()->after('drug_name');
            });
        }

        try {
            Schema::table('prescription_diagnosis_template_items', function (Blueprint $table) {
                $table->index('medicine_id', 'pdti_medicine_id_index');
            });
        } catch (\Throwable $e) {
            /* Index may already exist from a partially applied migration */
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prescription_diagnosis_template_items', function (Blueprint $table) {
            try {
                $table->dropIndex('pdti_medicine_id_index');
            } catch (\Throwable $e) {
                //
            }
            if (Schema::hasColumn('prescription_diagnosis_template_items', 'medicine_id')) {
                $table->dropColumn('medicine_id');
            }
        });
    }
}

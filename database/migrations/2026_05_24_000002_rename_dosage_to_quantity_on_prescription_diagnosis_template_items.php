<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameDosageToQuantityOnPrescriptionDiagnosisTemplateItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('prescription_diagnosis_template_items', 'dosage')
            && !Schema::hasColumn('prescription_diagnosis_template_items', 'quantity')) {
            DB::statement('ALTER TABLE `prescription_diagnosis_template_items` CHANGE `dosage` `quantity` VARCHAR(255) NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('prescription_diagnosis_template_items', 'quantity')
            && !Schema::hasColumn('prescription_diagnosis_template_items', 'dosage')) {
            DB::statement('ALTER TABLE `prescription_diagnosis_template_items` CHANGE `quantity` `dosage` VARCHAR(255) NULL');
        }
    }
}

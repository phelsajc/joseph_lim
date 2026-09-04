<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDosageToPrescriptionDiagnosisTemplateItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prescription_diagnosis_template_items', function (Blueprint $table) {
            if (!Schema::hasColumn('prescription_diagnosis_template_items', 'dosage')) {
                $table->string('dosage', 255)->nullable()->after('quantity');
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
        Schema::table('prescription_diagnosis_template_items', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_diagnosis_template_items', 'dosage')) {
                $table->dropColumn('dosage');
            }
        });
    }
}

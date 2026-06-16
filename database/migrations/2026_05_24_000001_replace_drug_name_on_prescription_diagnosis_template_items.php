<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceDrugNameOnPrescriptionDiagnosisTemplateItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prescription_diagnosis_template_items', function (Blueprint $table) {
            $table->string('brand_name', 500)->nullable()->after('medicine_id');
            $table->string('generic_name', 500)->nullable()->after('brand_name');
        });

        DB::table('prescription_diagnosis_template_items')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $drugName = trim((string) ($row->drug_name ?? ''));
                $hasMaster = !empty($row->medicine_id);

                DB::table('prescription_diagnosis_template_items')
                    ->where('id', $row->id)
                    ->update([
                        'brand_name' => $drugName !== '' ? $drugName : null,
                        'generic_name' => $hasMaster ? null : ($drugName !== '' ? $drugName : null),
                    ]);
            }
        });

        Schema::table('prescription_diagnosis_template_items', function (Blueprint $table) {
            $table->dropColumn('drug_name');
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
            $table->string('drug_name', 500)->nullable()->after('medicine_id');
        });

        DB::table('prescription_diagnosis_template_items')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $brand = trim((string) ($row->brand_name ?? ''));
                $generic = trim((string) ($row->generic_name ?? ''));
                $drugName = $brand !== '' ? $brand : $generic;

                DB::table('prescription_diagnosis_template_items')
                    ->where('id', $row->id)
                    ->update([
                        'drug_name' => $drugName !== '' ? $drugName : null,
                    ]);
            }
        });

        Schema::table('prescription_diagnosis_template_items', function (Blueprint $table) {
            $table->dropColumn(['brand_name', 'generic_name']);
        });
    }
}

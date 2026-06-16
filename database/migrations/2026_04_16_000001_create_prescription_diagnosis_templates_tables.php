<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrescriptionDiagnosisTemplatesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prescription_diagnosis_templates', function (Blueprint $table) {
            $table->id();
            $table->string('diagnosis_name', 500);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('diagnosis_name');
        });

        Schema::create('prescription_diagnosis_template_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_diagnosis_template_id');
            $table->string('drug_name', 500);
            $table->string('dosage', 255)->nullable();
            $table->string('frequency', 255)->nullable();
            $table->string('duration', 255)->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('prescription_diagnosis_template_id', 'rx_dx_tpl_items_tpl_id_fk')
                ->references('id')
                ->on('prescription_diagnosis_templates')
                ->onDelete('cascade');
            $table->index(['prescription_diagnosis_template_id', 'sort_order'], 'rx_dx_tpl_items_tpl_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prescription_diagnosis_template_items');
        Schema::dropIfExists('prescription_diagnosis_templates');
    }
}

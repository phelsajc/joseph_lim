<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiagnosticTemplatesTables extends Migration
{
    public function up()
    {
        Schema::create('diagnostic_templates', function (Blueprint $table) {
            $table->id();
            $table->string('diagnosis_name', 500);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('diagnosis_name');
        });

        Schema::create('diagnostic_template_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diagnostic_template_id');
            $table->string('diagnostic_name', 500);
            $table->string('category', 255)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('priority')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('diagnostic_template_id', 'dx_tpl_items_tpl_id_fk')
                ->references('id')
                ->on('diagnostic_templates')
                ->onDelete('cascade');
            $table->index(['diagnostic_template_id', 'sort_order'], 'dx_tpl_items_tpl_sort_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('diagnostic_template_items');
        Schema::dropIfExists('diagnostic_templates');
    }
}


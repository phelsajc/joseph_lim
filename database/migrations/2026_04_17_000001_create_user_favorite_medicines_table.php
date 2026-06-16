<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserFavoriteMedicinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_favorite_medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('medicine_id')->nullable();
            $table->string('drug_name', 500);
            $table->string('custom_generic_name', 500)->nullable();
            $table->string('default_qty', 64)->nullable();
            $table->string('default_bf_b', 64)->nullable();
            $table->string('default_bf_a', 64)->nullable();
            $table->string('default_l_b', 64)->nullable();
            $table->string('default_l_a', 64)->nullable();
            $table->string('default_s_b', 64)->nullable();
            $table->string('default_s_a', 64)->nullable();
            $table->string('default_bt', 64)->nullable();
            $table->string('default_dosage', 255)->nullable();
            $table->string('default_frequency', 255)->nullable();
            $table->string('default_duration', 255)->nullable();
            $table->text('default_remarks')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'medicine_id'], 'ufm_user_med_idx');
            $table->index(['user_id', 'drug_name'], 'ufm_user_drug_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_favorite_medicines');
    }
}

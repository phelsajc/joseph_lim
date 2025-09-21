<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDashboardPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add indexes for dashboard performance optimization
        Schema::table('appointments', function (Blueprint $table) {
            // Composite index for appointment date and cancel status
            $table->index(['appointment_dt', 'is_cancel'], 'idx_appointments_dt_cancel');
            
            // Index for patient ID lookups
            $table->index('patientid', 'idx_appointments_patientid');
        });
        
        Schema::table('patients', function (Blueprint $table) {
            // Index for isdeleted filter
            $table->index('isdeleted', 'idx_patients_isdeleted');
        });
        
        Schema::table('servicesrendered', function (Blueprint $table) {
            // Index for appointment ID joins
            $table->index('appointment_id', 'idx_servicesrendered_appointment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_appointments_dt_cancel');
            $table->dropIndex('idx_appointments_patientid');
        });
        
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_isdeleted');
        });
        
        Schema::table('servicesrendered', function (Blueprint $table) {
            $table->dropIndex('idx_servicesrendered_appointment_id');
        });
    }
}

<?php
/**
 * Dashboard Performance Test Script
 * Run this to test the performance improvements
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// Test the old vs new dashboard performance
class DashboardPerformanceTest
{
    public function testOldDashboard()
    {
        echo "Testing OLD dashboard method...\n";
        
        $start = microtime(true);
        DB::enableQueryLog();
        
        // Simulate the old method (with N+1 queries)
        $count_px = DB::table('patients')->where('isdeleted', 0)->get();
        $count_meds = DB::table('medicines')->get();
        $count_diagnostics = DB::table('services')->get();
        
        $todays_appt = DB::table('appointments')
            ->where('appointment_dt', date("Y-m-d"))
            ->where('is_cancel', 0)
            ->get();
        
        // Simulate N+1 queries (the main performance killer)
        foreach ($todays_appt as $appt) {
            $patient = DB::table('patients')->where('patientid', $appt->patientid)->first();
        }
        
        $calendar = DB::table('appointments')
            ->where('is_cancel', 0)
            ->get();
        
        // More N+1 queries
        foreach ($calendar as $cal) {
            $patient = DB::table('patients')->where('patientid', $cal->patientid)->first();
        }
        
        $end = microtime(true);
        $queries = DB::getQueryLog();
        
        echo "OLD Method Results:\n";
        echo "Time: " . round($end - $start, 4) . " seconds\n";
        echo "Queries: " . count($queries) . "\n";
        echo "Memory: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n\n";
        
        return $end - $start;
    }
    
    public function testNewDashboard()
    {
        echo "Testing NEW optimized dashboard method...\n";
        
        $start = microtime(true);
        DB::enableQueryLog();
        
        // Optimized queries
        $count_px = DB::table('patients')->where('isdeleted', 0)->count();
        $count_meds = DB::table('medicines')->count();
        $count_diagnostics = DB::table('services')->count();
        
        // Single JOIN query instead of N+1
        $todays_appt = DB::table('appointments')
            ->join('patients', 'appointments.patientid', '=', 'patients.patientid')
            ->where('appointments.appointment_dt', date("Y-m-d"))
            ->where('appointments.is_cancel', 0)
            ->select('appointments.*', 'patients.patientname')
            ->get();
        
        // Single JOIN query for calendar
        $calendar = DB::table('appointments')
            ->join('patients', 'appointments.patientid', '=', 'patients.patientid')
            ->where('appointments.is_cancel', 0)
            ->select('appointments.appointment_dt', 'patients.patientname')
            ->get();
        
        $end = microtime(true);
        $queries = DB::getQueryLog();
        
        echo "NEW Method Results:\n";
        echo "Time: " . round($end - $start, 4) . " seconds\n";
        echo "Queries: " . count($queries) . "\n";
        echo "Memory: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n\n";
        
        return $end - $start;
    }
    
    public function runTest()
    {
        echo "=== Dashboard Performance Test ===\n\n";
        
        $oldTime = $this->testOldDashboard();
        $newTime = $this->testNewDashboard();
        
        $improvement = (($oldTime - $newTime) / $oldTime) * 100;
        
        echo "=== Performance Comparison ===\n";
        echo "Old Method: " . round($oldTime, 4) . " seconds\n";
        echo "New Method: " . round($newTime, 4) . " seconds\n";
        echo "Improvement: " . round($improvement, 2) . "% faster\n";
        
        if ($improvement > 80) {
            echo "✅ Excellent performance improvement!\n";
        } elseif ($improvement > 50) {
            echo "✅ Good performance improvement!\n";
        } else {
            echo "⚠️  Performance improvement could be better.\n";
        }
    }
}

// Run the test
$test = new DashboardPerformanceTest();
$test->runTest();

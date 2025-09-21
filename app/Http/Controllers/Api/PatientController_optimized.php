<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientsResource;
use App\Model\Patients;
use App\Model\Medicine;
use App\Model\Services;
use App\Model\Appointments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PatientControllerOptimized extends Controller
{
    /**
     * Optimized Dashboard method with caching and efficient queries
     */
    public function dashboard()
    {
        date_default_timezone_set('Asia/Manila');
        
        // Use caching to avoid repeated expensive operations
        $cacheKey = 'dashboard_data_' . date('Y-m-d');
        $cacheDuration = 300; // 5 minutes cache
        
        return Cache::remember($cacheKey, $cacheDuration, function () {
            // Use count() instead of get()->count() for better performance
            $count_px = Patients::where('isdeleted', 0)->count();
            $count_meds = Medicine::count();
            $count_diagnostics = Services::count();
            
            // Optimize today's appointments query
            $todays_appt = DB::table('appointments')
                ->join('patients', 'appointments.patientid', '=', 'patients.patientid')
                ->where('appointments.appointment_dt', date("Y-m-d"))
                ->where('appointments.is_cancel', 0)
                ->select(
                    'appointments.patientid',
                    'appointments.chiefcomplaints',
                    'appointments.appointment_time',
                    'patients.patientname'
                )
                ->orderBy('appointments.appointment_dt', 'asc')
                ->get();
            
            // Optimize graph census query
            $graph_census = DB::table('appointments')
                ->selectRaw('count(appointment_dt) as cnt, DATE_FORMAT(appointment_dt, "%Y-%m") as apt_dt')
                ->where('is_cancel', 0)
                ->groupBy(DB::raw('DATE_FORMAT(appointment_dt, "%Y-%m")'))
                ->orderBy('apt_dt', 'asc')
                ->get();
            
            // Optimize calendar query with patient names in one query
            $calendar = DB::table('appointments')
                ->join('patients', 'appointments.patientid', '=', 'patients.patientid')
                ->where('appointments.is_cancel', 0)
                ->select(
                    'appointments.appointment_dt',
                    'appointments.patientid',
                    'patients.patientname'
                )
                ->groupBy('appointments.appointment_dt', 'appointments.patientid', 'patients.patientname')
                ->get();
            
            // Process graph data
            $data_graph = [];
            $data_graph_month = [];
            foreach ($graph_census as $value) {
                $data_graph[] = $value->cnt;
                $data_graph_month[] = date_format(date_create($value->apt_dt), 'F Y');
            }
            
            // Process calendar data (no more N+1 queries!)
            $data_calendar = [];
            foreach ($calendar as $value) {
                $data_calendar[] = [
                    'title' => $value->patientname ?: 'Unknown Patient',
                    'start' => date_format(date_create($value->appointment_dt), 'Y-m-d')
                ];
            }
            
            // Process today's patients (no more N+1 queries!)
            $data_todays_pxs = [];
            foreach ($todays_appt as $value) {
                $data_todays_pxs[] = [
                    'patient' => $value->patientname ?: 'Unknown Patient',
                    'complaints' => $value->chiefcomplaints,
                    'appointment_time' => $value->appointment_time
                ];
            }
            
            // Optimize revenue query
            $graph_revenue = DB::table('appointments')
                ->leftJoin('servicesrendered', 'appointments.id', '=', 'servicesrendered.appointment_id')
                ->where('appointments.is_cancel', 0)
                ->selectRaw('
                    COALESCE(SUM(servicesrendered.fee), 0) as amt,
                    COALESCE(SUM(appointments.discount), 0) as discount,
                    DATE_FORMAT(appointments.appointment_dt, "%Y-%m") as apt_dt
                ')
                ->groupBy(DB::raw('DATE_FORMAT(appointments.appointment_dt, "%Y-%m")'))
                ->orderBy('apt_dt', 'asc')
                ->get();
            
            // Process revenue data
            $revenue_arr = [];
            $revenue_month_arr = [];
            foreach ($graph_revenue as $value) {
                $revenue_arr[] = max(0, ($value->amt ?: 0) - ($value->discount ?: 0));
                $revenue_month_arr[] = date_format(date_create($value->apt_dt), 'F Y');
            }
            
            return [
                'graph_amt' => [["name" => 'Total', 'data' => $revenue_arr]],
                'revenue_mon' => $revenue_month_arr,
                'todaysAppt' => $data_todays_pxs,
                'calendar' => $data_calendar,
                'graph_census' => [["name" => 'No. of Patients', 'data' => $data_graph]],
                'data_graph_month' => $data_graph_month,
                'appt' => $todays_appt->count(),
                'patients' => $count_px,
                'meds' => $count_meds,
                'dx' => $count_diagnostics
            ];
        });
    }
    
    /**
     * Alternative dashboard method with even more optimizations
     * Use this if you want to remove caching but still optimize queries
     */
    public function dashboardOptimizedNoCache()
    {
        date_default_timezone_set('Asia/Manila');
        
        // Single query to get all counts
        $counts = DB::select("
            SELECT 
                (SELECT COUNT(*) FROM patients WHERE isdeleted = 0) as patients_count,
                (SELECT COUNT(*) FROM medicines) as medicines_count,
                (SELECT COUNT(*) FROM services) as diagnostics_count
        ")[0];
        
        // Single query for today's appointments with patient names
        $todays_appt = DB::table('appointments')
            ->join('patients', 'appointments.patientid', '=', 'patients.patientid')
            ->where('appointments.appointment_dt', date("Y-m-d"))
            ->where('appointments.is_cancel', 0)
            ->select(
                'appointments.patientid',
                'appointments.chiefcomplaints',
                'appointments.appointment_time',
                'patients.patientname'
            )
            ->orderBy('appointments.appointment_dt', 'asc')
            ->get();
        
        // Single query for graph data
        $graph_census = DB::table('appointments')
            ->selectRaw('count(appointment_dt) as cnt, DATE_FORMAT(appointment_dt, "%Y-%m") as apt_dt')
            ->where('is_cancel', 0)
            ->groupBy(DB::raw('DATE_FORMAT(appointment_dt, "%Y-%m")'))
            ->orderBy('apt_dt', 'asc')
            ->get();
        
        // Single query for calendar data
        $calendar = DB::table('appointments')
            ->join('patients', 'appointments.patientid', '=', 'patients.patientid')
            ->where('appointments.is_cancel', 0)
            ->select(
                'appointments.appointment_dt',
                'appointments.patientid',
                'patients.patientname'
            )
            ->groupBy('appointments.appointment_dt', 'appointments.patientid', 'patients.patientname')
            ->get();
        
        // Single query for revenue data
        $graph_revenue = DB::table('appointments')
            ->leftJoin('servicesrendered', 'appointments.id', '=', 'servicesrendered.appointment_id')
            ->where('appointments.is_cancel', 0)
            ->selectRaw('
                COALESCE(SUM(servicesrendered.fee), 0) as amt,
                COALESCE(SUM(appointments.discount), 0) as discount,
                DATE_FORMAT(appointments.appointment_dt, "%Y-%m") as apt_dt
            ')
            ->groupBy(DB::raw('DATE_FORMAT(appointments.appointment_dt, "%Y-%m")'))
            ->orderBy('apt_dt', 'asc')
            ->get();
        
        // Process data efficiently
        $data_graph = $graph_census->pluck('cnt')->toArray();
        $data_graph_month = $graph_census->map(function($item) {
            return date_format(date_create($item->apt_dt), 'F Y');
        })->toArray();
        
        $data_calendar = $calendar->map(function($item) {
            return [
                'title' => $item->patientname ?: 'Unknown Patient',
                'start' => date_format(date_create($item->appointment_dt), 'Y-m-d')
            ];
        })->toArray();
        
        $data_todays_pxs = $todays_appt->map(function($item) {
            return [
                'patient' => $item->patientname ?: 'Unknown Patient',
                'complaints' => $item->chiefcomplaints,
                'appointment_time' => $item->appointment_time
            ];
        })->toArray();
        
        $revenue_arr = $graph_revenue->map(function($item) {
            return max(0, ($item->amt ?: 0) - ($item->discount ?: 0));
        })->toArray();
        
        $revenue_month_arr = $graph_revenue->map(function($item) {
            return date_format(date_create($item->apt_dt), 'F Y');
        })->toArray();
        
        return response()->json([
            'graph_amt' => [["name" => 'Total', 'data' => $revenue_arr]],
            'revenue_mon' => $revenue_month_arr,
            'todaysAppt' => $data_todays_pxs,
            'calendar' => $data_calendar,
            'graph_census' => [["name" => 'No. of Patients', 'data' => $data_graph]],
            'data_graph_month' => $data_graph_month,
            'appt' => $todays_appt->count(),
            'patients' => $counts->patients_count,
            'meds' => $counts->medicines_count,
            'dx' => $counts->diagnostics_count
        ]);
    }
}

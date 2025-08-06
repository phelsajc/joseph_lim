<?php
namespace App\Helpers;
use App\Model\Patients;
use App\Model\Medicine;
class Helpers
{
    public static function patientDetail($id)
    {
        $patient_detail = Patients::where(["patientid"=>$id])->first();
        return $patient_detail;
    }
    public static function medicineDetail($id)
    {
        /* $medicineDetail = Medicine::join('generics', 'generics.id', '=', 'medicines.generic_id')
        ->select('medicines.*', 'generics.generic_name') // Add other columns you need
        ->where('medicines.id', $id) // Add any additional conditions you need
        ->first(); */

        $medicineDetail = Medicine::where('medicines.id', $id)
        ->leftJoin('generics', function ($query) {
            $query->on('generics.id', '=', 'medicines.generic_id');
        })
        ->first();
        return $medicineDetail;
    }
}
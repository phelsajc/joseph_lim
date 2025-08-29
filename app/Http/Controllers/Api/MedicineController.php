<?php
/**
 * File LaravelController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;
use App\Model\Patients;
use App\Model\Medicine;
use App\Model\Generics;
use App\Model\Appointments;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Resources\PatientsResource;
use App\Http\Resources\MedicineResource;
use App\Http\Resources\AppointmentResource;
use App\Laravue\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use DB;

/**
 * Class LaravueController
 *
 * @package App\Http\Controllers
 */
class MedicineController extends BaseController
{
    const ITEM_PER_PAGE = 15;
    /**
     * Entry point for Laravue Dashboard
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function index1(Request $request)
    {
        $searchParams = $request->all();
        $userQuery = Medicine::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $userQuery->whereRaw('LOWER(medicine_name) LIKE ?', ['%'.$keyword.'%']);
        }
        return MedicineResource::collection($userQuery->paginate($limit));
    }

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        $userQuery = Medicine::query()
            ->select('medicines.generic_name', 'medicines.medicine_name', 'medicines.unit', 'medicines.id')
            ->where('isincluded', 1) // ✅ added filter
            ->join(DB::raw("(SELECT MIN(id) as id 
                            FROM medicines
                            " . (!empty($keyword) 
                                    ? "WHERE medicine_name LIKE '%".addslashes($keyword)."%' 
                                        OR generic_name LIKE '%".addslashes($keyword)."%'" 
                                    : "") . "
                            GROUP BY medicine_name) as filtered"), 
                    'medicines.id', '=', 'filtered.id');

        return MedicineResource::collection($userQuery->paginate($limit));
    }


    public function findMedicine($kw)
    {
        $q = DB::connection('mysql')->select("SELECT m.generic_name, m.medicine_name, m.unit, m.id
        FROM medicines m
        JOIN (
            SELECT MIN(id) AS id
            FROM medicines
            WHERE (medicine_name LIKE '%$kw%' OR generic_name LIKE '%$kw%')
              AND isincluded = 1
            GROUP BY medicine_name
        ) AS filtered
        ON m.id = filtered.id
        LIMIT 10;");
        $data = array();
        foreach ($q as $key => $value) {
            $arr =  array();
            //$arr['medicine'] = $value->generic_name.' ('.$value->medicine_name.') '.$value->unit;
            $arr['medicine'] = $value->medicine_name;
            $arr['id'] = $value->id;
            $data[] = $arr;
        }
        return response()->json(['suggestions' => $data]);
    }
    
    public function store(Request $request) {
        $field = new Medicine();
        $field->medicine_name = $request->medicine_name	;
        //$field->generic_name = $request->generic_name;
        $field->isincluded = 1;
        $field->save();
        return response()->json(true);
    }

    public function update(Request $request) {
        $field = Medicine::find($request->id);
        $field->medicine_name = $request->medicine_name	;
        //$field->generic_name = $request->generic_name;
        $field->isincluded = 1;
        $field->save();
        return response()->json(true);
    }

    function delete($id) {
        $field = Medicine::find($id);
        $field->isincluded = 0;
        $field->save();
        return response()->json( $field);
    }
    
    function edit($id) {
        $data = Medicine::where('id',$id)->first();
        return response()->json($data);
    }
}

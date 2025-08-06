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
class AncillaryController extends BaseController
{
    const ITEM_PER_PAGE = 15;
    /**
     * Entry point for Laravue Dashboard
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function index(Request $request)
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

    public function findprocedure($kw)
    {
        $q = DB::connection('mysql')->select("select * from lab_tests where LOWER(lab_test) like '%$kw%' limit 10");
        
        $data1 = array();
        foreach ($q as $key => $value) {
            $arr =  array();
            $arr['procedure'] = $value->lab_test;
            $arr['id'] = $value->lab_test_id;
            $arr['type'] = $value->lab_category_id;
            $data1[] = $arr;
        }
        /* $p = DB::connection('mysql')->select("select * from services where LOWER(description) like '%$kw%' limit 10");
        $data2 = array();
        foreach ($p as $key => $value) {
            $arr =  array();
            $arr['procedure'] = $value->description;
            $arr['id'] = $value->service_id;
            $arr['type'] = 'imgaging';
            $data2[] = $arr;
        } */
        //return response()->json(['suggestions' => array_merge($data1,$data2)]);
        return response()->json(['suggestions' => $data1]);
    }
}

<?php
/**
 * File LaravelController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;
use App\Model\Services;
use App\Model\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Resources\MedicineResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use DB;

/**
 * Class LaravueController
 *
 * @package App\Http\Controllers
 */
class ServicesController extends BaseController
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

    public function getAllServices()
    {
        $services = Services::orderBy('description','asc')->get();
        return response()->json($services);
    }

    public function findservices($kw)
    {
        $p = DB::connection('mysql')->select("select * from services where LOWER(description) like '%$kw%' limit 10");
        $data = array();
        foreach ($p as $key => $value) {
            $arr =  array();
            $arr['service'] = $value->description;
            $arr['id'] = $value->service_id;
            $arr['fee'] = $value->fee;
            $data[] = $arr;
        }
        //return response()->json(['suggestions' => array_merge($data1,$data2)]);
        return response()->json(['suggestions' => $data]);
    }
}

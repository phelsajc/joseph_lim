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
use App\Http\Resources\ServicesResource;
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
        $userQuery = Services::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $userQuery->whereRaw('LOWER(description) LIKE ?', ['%'.$keyword.'%']);
        }
        return ServicesResource::collection($userQuery->paginate($limit));
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
        return response()->json(['suggestions' => $data]);
    }

    public function store(Request $request) {
        $field = new Services();
        $field->description = $request->service;
        $field->fee = $request->fee;
        $field->save();
        return response()->json(true);
    }

    public function update(Request $request) {
        $field = Services::find($request->id);
        $field->description = $request->service;
        $field->fee = $request->fee;
        $field->save();
        return response()->json(true);
    }

    function delete($id) {
        Services::find($id)->delete();
        return response()->json(true);
    }

    function edit($id) {
        $data = Services::where('service_id ',$id)->first();
        return response()->json($data);
    }
}

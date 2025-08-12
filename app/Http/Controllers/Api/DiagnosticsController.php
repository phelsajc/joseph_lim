<?php
/**
 * File LaravelController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;
use App\Model\Labs;
use App\Model\Generics;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Resources\DiagnosticsResource;
use App\Laravue\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use DB;

/**
 * Class LaravueController
 *
 * @package App\Http\Controllers
 */
class DiagnosticsController extends BaseController
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
        $userQuery = Labs::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $userQuery->whereRaw('LOWER(lab_test) LIKE ?', ['%'.strtolower($keyword).'%']);
        }
        return DiagnosticsResource::collection($userQuery->paginate($limit));
    }

    public function store(Request $request) {
        $field = new Labs();
        $field->lab_test = $request->diagnostic;
        $field->lab_category_id = $request->type;
        $field->save();
        return response()->json(true);
    }

    public function update(Request $request) {
        $field = Labs::find($request->id);
        $field->lab_test = $request->diagnostic;
        $field->lab_category_id = $request->type;
        $field->save();
        return response()->json(true);
    }

    function delete($id) {
        Labs::where('id',$id)->delete();
        return response()->json(true);
    }

    function edit($id) {
        $data = Labs::where('id',$id)->first();
        return response()->json($data);
    }
    
    public function getAllDiagnostics()
    {
        $services = Labs::where(['isactive'=>1])->get();
        return response()->json($services);
    }
}

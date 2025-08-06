<?php
/**
 * File LaravelController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;
use App\Model\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class LaravueController
 *
 * @package App\Http\Controllers
 */
class ProfileController extends BaseController
{
    const ITEM_PER_PAGE = 15;
    /**
     * Entry point for Laravue Dashboard
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    
     public function update(Request $request) {
        date_default_timezone_set('Asia/Manila');
        $field = Profile::find($request->id);
        $field->name = $request->name;
        $field->prc = $request->prc;
        $field->prc_validity = date_format(date_create($request->prc_validity),'Y-m-d');
        $field->ptr = $request->ptr;
        $field->ptr_validity = date_format(date_create($request->ptr_validity),'Y-m-d');
        $field->signature = $request->signatureData;
        $field->specialization1 = $request->spcl1;
        $field->specialization2 = $request->spcl2;
        $field->pic = $request->pic;
        $field->save();
        return response()->json(true);
    }

    function getProfile() {
        $data = Profile::where('id','1')->first();
        return response()->json($data);
    }
    
    public function removeSignature($id) {
        $field = Profile::find($id);
        $field->signature = null;
        $field->save();
        return response()->json(true);
    }
}

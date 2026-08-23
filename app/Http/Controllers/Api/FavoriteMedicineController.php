<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\UserFavoriteMedicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteMedicineController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $rows = UserFavoriteMedicine::query()
            ->from('user_favorite_medicines')
            ->leftJoin('medicines', 'user_favorite_medicines.medicine_id', '=', 'medicines.id')
            ->where('user_favorite_medicines.user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('user_favorite_medicines.medicine_id')
                    ->orWhere('medicines.isincluded', 1);
            })
            ->orderBy('user_favorite_medicines.drug_name')
            ->select('user_favorite_medicines.*')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'drug_name' => 'required|string|max:500',
            'medicine_id' => 'nullable|integer|min:0',
            'custom_generic_name' => 'nullable|string|max:500',
            'default_qty' => 'nullable|string|max:64',
            'default_bf_b' => 'nullable|string|max:64',
            'default_bf_a' => 'nullable|string|max:64',
            'default_l_b' => 'nullable|string|max:64',
            'default_l_a' => 'nullable|string|max:64',
            'default_s_b' => 'nullable|string|max:64',
            'default_s_a' => 'nullable|string|max:64',
            'default_bt' => 'nullable|string|max:64',
            'default_dosage' => 'nullable|string|max:255',
            'default_frequency' => 'nullable|string|max:255',
            'default_duration' => 'nullable|string|max:255',
            'default_remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $medicineId = $request->input('medicine_id');
        if ($medicineId === 0 || $medicineId === '0') {
            $medicineId = null;
        }

        $drugName = trim((string) $request->input('drug_name'));
        if ($drugName === '') {
            return response()->json(['message' => 'drug_name is required'], 422);
        }

        $query = UserFavoriteMedicine::where('user_id', $user->id);
        if ($medicineId !== null) {
            $query->where('medicine_id', $medicineId);
        } else {
            $query->whereNull('medicine_id')->where('drug_name', $drugName);
        }

        $existing = $query->first();
        if ($existing) {
            return response()->json(['data' => $existing, 'existing' => true]);
        }

        $row = new UserFavoriteMedicine();
        $row->user_id = $user->id;
        $row->medicine_id = $medicineId;
        $row->drug_name = $drugName;
        $row->custom_generic_name = $request->input('custom_generic_name');
        $row->default_qty = $request->input('default_qty');
        $row->default_bf_b = $request->input('default_bf_b');
        $row->default_bf_a = $request->input('default_bf_a');
        $row->default_l_b = $request->input('default_l_b');
        $row->default_l_a = $request->input('default_l_a');
        $row->default_s_b = $request->input('default_s_b');
        $row->default_s_a = $request->input('default_s_a');
        $row->default_bt = $request->input('default_bt');
        $row->default_dosage = $request->input('default_dosage');
        $row->default_frequency = $request->input('default_frequency');
        $row->default_duration = $request->input('default_duration');
        $row->default_remarks = $request->input('default_remarks');
        $row->save();

        return response()->json(['data' => $row, 'existing' => false]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $row = UserFavoriteMedicine::where('user_id', $user->id)->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'drug_name' => 'sometimes|required|string|max:500',
            'medicine_id' => 'nullable|integer|min:0',
            'custom_generic_name' => 'nullable|string|max:500',
            'default_qty' => 'nullable|string|max:64',
            'default_bf_b' => 'nullable|string|max:64',
            'default_bf_a' => 'nullable|string|max:64',
            'default_l_b' => 'nullable|string|max:64',
            'default_l_a' => 'nullable|string|max:64',
            'default_s_b' => 'nullable|string|max:64',
            'default_s_a' => 'nullable|string|max:64',
            'default_bt' => 'nullable|string|max:64',
            'default_dosage' => 'nullable|string|max:255',
            'default_frequency' => 'nullable|string|max:255',
            'default_duration' => 'nullable|string|max:255',
            'default_remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fill = $request->only([
            'drug_name',
            'medicine_id',
            'custom_generic_name',
            'default_qty',
            'default_bf_b',
            'default_bf_a',
            'default_l_b',
            'default_l_a',
            'default_s_b',
            'default_s_a',
            'default_bt',
            'default_dosage',
            'default_frequency',
            'default_duration',
            'default_remarks',
        ]);

        if (array_key_exists('medicine_id', $fill)) {
            if ($fill['medicine_id'] === 0 || $fill['medicine_id'] === '0') {
                $fill['medicine_id'] = null;
            }
        }

        $row->fill($fill);
        $row->save();

        return response()->json(['data' => $row]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $deleted = UserFavoriteMedicine::where('user_id', $user->id)->where('id', $id)->delete();

        return response()->json(['success' => (bool) $deleted]);
    }
}

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
use Illuminate\Support\Facades\Schema;
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
     * Admin list — one row per medicine record with defaults for appointments.
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        $userQuery = Medicine::query();

        if (!empty($keyword)) {
            $kw = '%' . addcslashes($keyword, '%_\\') . '%';
            $userQuery->where(function ($q) use ($kw) {
                $q->where('medicine_name', 'like', $kw)
                    ->orWhere('generic_name', 'like', $kw);
            });
        }

        $userQuery->orderByDesc('id');

        return MedicineResource::collection($userQuery->paginate($limit));
    }

    public function findMedicine($kw)
    {
        $like = '%' . addcslashes((string) $kw, '%_\\') . '%';
        $q = DB::connection('mysql')->select(
            'SELECT m.generic_name, m.medicine_name, m.unit, m.id,
                m.default_qty, m.default_bf_b, m.default_bf_a,
                m.default_l_b, m.default_l_a, m.default_s_b, m.default_s_a,
                m.default_bt, m.default_remarks
            FROM medicines m
            JOIN (
                /* Prefer latest row per brand so defaults edited in admin match search/get-meds. */
                SELECT MAX(id) AS id
                FROM medicines
                WHERE (medicine_name LIKE ? OR generic_name LIKE ?)
                  AND isincluded = 1
                GROUP BY medicine_name
            ) AS filtered
            ON m.id = filtered.id
            ORDER BY m.medicine_name ASC
            LIMIT 10',
            [$like, $like]
        );
        $data = [];
        foreach ($q as $value) {
            $data[] = [
                'medicine' => $value->medicine_name,
                'id' => $value->id,
                'generic_name' => $value->generic_name,
                'unit' => $value->unit,
                'default_qty' => $value->default_qty,
                'default_bf_b' => $value->default_bf_b,
                'default_bf_a' => $value->default_bf_a,
                'default_l_b' => $value->default_l_b,
                'default_l_a' => $value->default_l_a,
                'default_s_b' => $value->default_s_b,
                'default_s_a' => $value->default_s_a,
                'default_bt' => $value->default_bt,
                'default_remarks' => $value->default_remarks,
            ];
        }

        return response()->json(['suggestions' => $data]);
    }

    public function store(Request $request)
    {
        $field = new Medicine();
        $this->fillMedicineFromRequest($field, $request);
        $field->isincluded = $request->input('isincluded', 1) ? 1 : 0;
        if (Schema::hasColumn('medicines', 'created_at')) {
            $field->created_at = now();
        }
        $field->save();
        return response()->json(true);
    }

    public function update(Request $request)
    {
        $field = Medicine::find($request->id);
        if (!$field) {
            return response()->json(['message' => 'Medicine not found'], 404);
        }
        $this->fillMedicineFromRequest($field, $request);
        if ($request->has('isincluded')) {
            $field->isincluded = $request->input('isincluded') ? 1 : 0;
        }
        $field->save();
        return response()->json(true);
    }

    function delete($id)
    {
        $field = Medicine::find($id);
        $field->isincluded = 0;
        $field->save();
        return response()->json($field);
    }

    function edit($id)
    {
        $data = Medicine::where('id', $id)->first();
        return response()->json($data);
    }

    private function fillMedicineFromRequest(Medicine $field, Request $request)
    {
        $brand = $request->input('medicine_name', $request->input('brand_name'));
        $field->medicine_name = $brand;
        if ($request->has('generic_name')) {
            $field->generic_name = $request->input('generic_name');
        }
        foreach ([
            'unit',
            'default_qty',
            'default_bf_b',
            'default_bf_a',
            'default_l_b',
            'default_l_a',
            'default_s_b',
            'default_s_a',
            'default_bt',
            'default_remarks',
        ] as $col) {
            if ($request->has($col)) {
                $field->{$col} = $request->input($col);
            }
        }
    }
}

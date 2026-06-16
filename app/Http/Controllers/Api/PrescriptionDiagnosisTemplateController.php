<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\PrescriptionDiagnosisTemplate;
use App\Model\PrescriptionDiagnosisTemplateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrescriptionDiagnosisTemplateController extends Controller
{
    const ITEM_PER_PAGE = 15;

    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', self::ITEM_PER_PAGE);
        $keyword = trim((string) $request->get('keyword', ''));

        $query = PrescriptionDiagnosisTemplate::query()
            ->withCount('items');

        if ($keyword !== '') {
            $query->where('diagnosis_name', 'like', '%'.$keyword.'%');
        }

        $paginator = $query->orderByDesc('updated_at')->paginate($limit);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    public function diagnosisSuggestions(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $base = PrescriptionDiagnosisTemplate::query()
            ->select('diagnosis_name')
            ->distinct();

        if ($q !== '') {
            $base->where('diagnosis_name', 'like', '%'.$q.'%');
        }

        $names = $base->orderBy('diagnosis_name')
            ->limit(30)
            ->pluck('diagnosis_name');

        return response()->json(['data' => $names]);
    }

    public function show($id)
    {
        $template = PrescriptionDiagnosisTemplate::with(['items' => function ($q) {
            $q->orderBy('sort_order');
        }])->find($id);

        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        return response()->json(['data' => $template]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $template = DB::transaction(function () use ($data) {
            $tpl = PrescriptionDiagnosisTemplate::create([
                'diagnosis_name' => $data['diagnosis_name'],
                'created_by' => Auth::id(),
            ]);

            $this->syncItems($tpl, $data['items']);

            return $tpl->load(['items' => function ($q) {
                $q->orderBy('sort_order');
            }]);
        });

        return response()->json(['data' => $template], 201);
    }

    public function update(Request $request, $id)
    {
        $template = PrescriptionDiagnosisTemplate::find($id);

        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $template = DB::transaction(function () use ($template, $data) {
            $template->update([
                'diagnosis_name' => $data['diagnosis_name'],
            ]);

            PrescriptionDiagnosisTemplateItem::where('prescription_diagnosis_template_id', $template->id)->delete();
            $this->syncItems($template, $data['items']);

            return $template->load(['items' => function ($q) {
                $q->orderBy('sort_order');
            }]);
        });

        return response()->json(['data' => $template]);
    }

    public function destroy($id)
    {
        $template = PrescriptionDiagnosisTemplate::find($id);

        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        $template->delete();

        return response()->json(['success' => true]);
    }

    private function rules(): array
    {
        return [
            'diagnosis_name' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.brand_name' => 'required|string|max:500',
            'items.*.generic_name' => 'nullable|string|max:500',
            'items.*.medicine_id' => 'nullable|integer|exists:medicines,id',
            'items.*.quantity' => 'nullable|string|max:255',
            'items.*.frequency' => 'nullable|string|max:255',
            'items.*.duration' => 'nullable|string|max:255',
            'items.*.instructions' => 'nullable|string|max:5000',
        ];
    }

    private function syncItems(PrescriptionDiagnosisTemplate $template, array $items): void
    {
        foreach ($items as $index => $row) {
            $medicineId = $row['medicine_id'] ?? null;
            $genericName = isset($row['generic_name']) ? trim((string) $row['generic_name']) : '';

            if (empty($medicineId) && $genericName === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items.'.$index.'.generic_name' => ['Generic name is required for custom medicines.'],
                ]);
            }

            PrescriptionDiagnosisTemplateItem::create([
                'prescription_diagnosis_template_id' => $template->id,
                'brand_name' => trim((string) $row['brand_name']),
                'generic_name' => $genericName !== '' ? $genericName : null,
                'medicine_id' => $medicineId,
                'quantity' => $row['quantity'] ?? null,
                'frequency' => $row['frequency'] ?? null,
                'duration' => $row['duration'] ?? null,
                'instructions' => $row['instructions'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }
}

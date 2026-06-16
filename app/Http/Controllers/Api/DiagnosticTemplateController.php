<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\DiagnosticTemplate;
use App\Model\DiagnosticTemplateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiagnosticTemplateController extends Controller
{
    const ITEM_PER_PAGE = 15;

    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', self::ITEM_PER_PAGE);
        $keyword = trim((string) $request->get('keyword', ''));

        $query = DiagnosticTemplate::query()
            ->withCount('items')
            ->with(['creator:id,name']);

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
        $base = DiagnosticTemplate::query()
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
        $template = DiagnosticTemplate::with([
            'creator:id,name',
            'items' => function ($q) {
                $q->orderBy('sort_order');
            },
        ])->find($id);

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
            $tpl = DiagnosticTemplate::create([
                'diagnosis_name' => $data['diagnosis_name'],
                'created_by' => Auth::id(),
            ]);

            $this->syncItems($tpl, $data['items']);

            return $tpl->load(['creator:id,name', 'items' => function ($q) {
                $q->orderBy('sort_order');
            }]);
        });

        return response()->json(['data' => $template], 201);
    }

    public function update(Request $request, $id)
    {
        $template = DiagnosticTemplate::find($id);

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

            DiagnosticTemplateItem::where('diagnostic_template_id', $template->id)->delete();
            $this->syncItems($template, $data['items']);

            return $template->load(['creator:id,name', 'items' => function ($q) {
                $q->orderBy('sort_order');
            }]);
        });

        return response()->json(['data' => $template]);
    }

    public function destroy($id)
    {
        $template = DiagnosticTemplate::find($id);

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
            'items.*.diagnostic_name' => 'required|string|max:500',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.notes' => 'nullable|string|max:5000',
            'items.*.priority' => 'nullable|integer|min:0|max:65535',
            'items.*.active' => 'nullable|boolean',
        ];
    }

    private function syncItems(DiagnosticTemplate $template, array $items): void
    {
        foreach ($items as $index => $row) {
            DiagnosticTemplateItem::create([
                'diagnostic_template_id' => $template->id,
                'diagnostic_name' => $row['diagnostic_name'],
                'category' => $row['category'] ?? null,
                'notes' => $row['notes'] ?? null,
                'priority' => $row['priority'] ?? null,
                'active' => array_key_exists('active', $row) ? (bool) $row['active'] : true,
                'sort_order' => $index,
            ]);
        }
    }
}


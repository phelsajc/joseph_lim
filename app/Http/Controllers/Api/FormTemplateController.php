<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\FormTemplate;
use App\Services\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FormTemplateController extends Controller
{
    const ITEM_PER_PAGE = 15;

    /** Predefined template types shown in category pickers. */
    const PREDEFINED_CATEGORIES = [
        'Medical Certificate',
        'Referral',
        'Admitting Letter',
        'PT Notes',
        'Consultation Form',
        'Clearance',
        'Others',
    ];

    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', self::ITEM_PER_PAGE);
        $keyword = trim((string) $request->get('keyword', ''));
        $category = trim((string) $request->get('category', ''));

        $query = FormTemplate::query()->with('creator:id,name');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('category', 'like', '%'.$keyword.'%')
                    ->orWhere('description', 'like', '%'.$keyword.'%');
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
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

    public function categories()
    {
        $fromDb = FormTemplate::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->all();

        $merged = array_values(array_unique(array_merge(
            self::PREDEFINED_CATEGORIES,
            $fromDb
        )));

        usort($merged, function ($a, $b) {
            $order = array_flip(self::PREDEFINED_CATEGORIES);
            $ia = isset($order[$a]) ? $order[$a] : 999;
            $ib = isset($order[$b]) ? $order[$b] : 999;
            if ($ia !== $ib) {
                return $ia <=> $ib;
            }

            return strcasecmp($a, $b);
        });

        return response()->json(['data' => $merged]);
    }

    public function show($id)
    {
        $template = FormTemplate::with('creator:id,name')->find($id);

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
        $html = RichTextSanitizer::purify($data['content_html'] ?? '');

        $template = FormTemplate::create([
            'name' => $data['name'],
            'category' => $this->nullIfEmpty($data['category'] ?? null),
            'description' => $this->nullIfEmpty($data['description'] ?? null),
            'content_html' => $html,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['data' => $template->load('creator:id,name')], 201);
    }

    public function update(Request $request, $id)
    {
        $template = FormTemplate::find($id);

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
        $html = RichTextSanitizer::purify($data['content_html'] ?? '');

        $template->update([
            'name' => $data['name'],
            'category' => $this->nullIfEmpty($data['category'] ?? null),
            'description' => $this->nullIfEmpty($data['description'] ?? null),
            'content_html' => $html,
        ]);

        return response()->json(['data' => $template->fresh()->load('creator:id,name')]);
    }

    public function destroy($id)
    {
        $template = FormTemplate::find($id);

        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        $template->delete();

        return response()->json(['success' => true]);
    }

    public function duplicate($id)
    {
        $source = FormTemplate::find($id);

        if (!$source) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        $copy = DB::transaction(function () use ($source) {
            $baseName = $source->name;
            $suffix = ' (copy)';
            $name = $baseName.$suffix;
            if (strlen($name) > 500) {
                $name = substr($baseName, 0, 500 - strlen($suffix)).$suffix;
            }

            return FormTemplate::create([
                'name' => $name,
                'category' => $source->category,
                'description' => $source->description,
                'content_html' => $source->content_html,
                'created_by' => Auth::id(),
            ]);
        });

        return response()->json(['data' => $copy->load('creator:id,name')], 201);
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:500',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content_html' => 'nullable|string|max:500000',
        ];
    }

    private function nullIfEmpty($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $t = trim((string) $value);

        return $t === '' ? null : $t;
    }
}

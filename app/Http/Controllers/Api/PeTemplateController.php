<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\PeTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PeTemplateController extends Controller
{
    /**
     * Get all active templates
     */
    public function index()
    {
        try {
            $templates = PeTemplate::active()
                ->orderBy('type', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get templates by type
     */
    public function getByType($type)
    {
        try {
            $templates = PeTemplate::active()
                ->byType($type)
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new custom template
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:pe_templates,name',
                'content' => 'required|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $template = PeTemplate::create([
                'name' => $request->name,
                'content' => $request->content,
                'type' => 'custom',
                'created_by' => Auth::id(),
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing template
     */
    public function update(Request $request, $id)
    {
        try {
            $template = PeTemplate::find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            // Only allow updates to custom templates
            if ($template->type === 'default') {
                return response()->json([
                    'success' => false,
                    'message' => 'Default templates cannot be modified'
                ], 403);
            }

            // Only allow updates by the creator or admin
            if ($template->created_by !== Auth::id() && !$this->checkAdminRole()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to modify this template'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:pe_templates,name,' . $id,
                'content' => 'required|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $template->update([
                'name' => $request->name,
                'content' => $request->content,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => $template
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a template
     */
    public function destroy($id)
    {
        try {
            $template = PeTemplate::find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            // Only allow deletion of custom templates
            if ($template->type === 'default') {
                return response()->json([
                    'success' => false,
                    'message' => 'Default templates cannot be deleted'
                ], 403);
            }

            // Only allow deletion by the creator or admin
            if ($template->created_by !== Auth::id() && !$this->checkAdminRole()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this template'
                ], 403);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle template active status (admin only)
     */
    public function toggleStatus($id)
    {
        try {
            if (!$this->checkAdminRole()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $template = PeTemplate::find($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $template->update([
                'is_active' => !$template->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template status updated successfully',
                'data' => $template
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if the current user has admin role
     */
    private function checkAdminRole()
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin']);
    }
}

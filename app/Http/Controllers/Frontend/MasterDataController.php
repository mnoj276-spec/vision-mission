<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\State;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class MasterDataController extends Controller
{
    protected function checkAdmin(): ?JsonResponse
    {
        if (Gate::denies('admin-access')) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden Access.'], 403);
        }
        return null;
    }

    protected function logAction(Request $request, string $action, string $details): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'N/A',
            'action' => $action,
            'details' => $details
        ]);
    }

    // --- CATEGORIES ---
    public function getCategories(): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        return response()->json(['status' => 'success', 'data' => Category::orderBy('name')->get()]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $request->validate(['name' => 'required|string|max:100|unique:categories,name']);

        $category = Category::create([
            'name' => $request->name,
            'slug' => str()->slug($request->name)
        ]);

        $this->logAction($request, 'Create Category', "Created category '{$category->name}' (ID: {$category->id})");
        return response()->json(['status' => 'success', 'message' => 'Category created successfully!', 'data' => $category]);
    }

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $category = Category::findOrFail($id);
        $request->validate(['name' => "required|string|max:100|unique:categories,name,{$id}"]);

        $category->update([
            'name' => $request->name,
            'slug' => str()->slug($request->name)
        ]);

        $this->logAction($request, 'Update Category', "Updated category to '{$category->name}' (ID: {$category->id})");
        return response()->json(['status' => 'success', 'message' => 'Category updated successfully!', 'data' => $category]);
    }

    public function deleteCategory(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $category = Category::findOrFail($id);
        
        // Safety guard: check if used in jobs
        if (\App\Models\JobPost::where('category_id', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete Category: It is currently linked to active recruitment posts.'], 400);
        }

        $name = $category->name;
        $category->delete();

        $this->logAction($request, 'Delete Category', "Deleted category '{$name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Category deleted successfully!']);
    }

    // --- DEPARTMENTS ---
    public function getDepartments(): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        return response()->json(['status' => 'success', 'data' => Department::orderBy('name')->get()]);
    }

    public function storeDepartment(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $request->validate([
            'name' => 'required|string|max:150|unique:departments,name',
            'code' => 'required|string|max:20|unique:departments,code'
        ]);

        $dept = Department::create([
            'name' => $request->name,
            'code' => strtoupper($request->code)
        ]);

        $this->logAction($request, 'Create Department', "Created department '{$dept->name}' ({$dept->code})");
        return response()->json(['status' => 'success', 'message' => 'Department created successfully!', 'data' => $dept]);
    }

    public function updateDepartment(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $dept = Department::findOrFail($id);
        $request->validate([
            'name' => "required|string|max:150|unique:departments,name,{$id}",
            'code' => "required|string|max:20|unique:departments,code,{$id}"
        ]);

        $dept->update([
            'name' => $request->name,
            'code' => strtoupper($request->code)
        ]);

        $this->logAction($request, 'Update Department', "Updated department to '{$dept->name}' ({$dept->code})");
        return response()->json(['status' => 'success', 'message' => 'Department updated successfully!', 'data' => $dept]);
    }

    public function deleteDepartment(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $dept = Department::findOrFail($id);

        if (\App\Models\JobPost::where('department_id', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete Department: It is linked to active recruitment posts.'], 400);
        }

        $name = $dept->name;
        $dept->delete();

        $this->logAction($request, 'Delete Department', "Deleted department '{$name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Department deleted successfully!']);
    }

    // --- QUALIFICATIONS ---
    public function getQualifications(): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        return response()->json(['status' => 'success', 'data' => Qualification::orderBy('id')->get()]);
    }

    public function storeQualification(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $request->validate(['name' => 'required|string|max:100|unique:qualifications,name']);

        $qual = Qualification::create([
            'name' => $request->name,
            'slug' => str()->slug($request->name)
        ]);

        $this->logAction($request, 'Create Qualification', "Created qualification '{$qual->name}'");
        return response()->json(['status' => 'success', 'message' => 'Qualification created successfully!', 'data' => $qual]);
    }

    public function updateQualification(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $qual = Qualification::findOrFail($id);
        $request->validate(['name' => "required|string|max:100|unique:qualifications,name,{$id}"]);

        $qual->update([
            'name' => $request->name,
            'slug' => str()->slug($request->name)
        ]);

        $this->logAction($request, 'Update Qualification', "Updated qualification to '{$qual->name}'");
        return response()->json(['status' => 'success', 'message' => 'Qualification updated successfully!', 'data' => $qual]);
    }

    public function deleteQualification(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $qual = Qualification::findOrFail($id);

        if (\App\Models\JobPost::where('qualification_id', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete Qualification: It is linked to active recruitment posts.'], 400);
        }

        $name = $qual->name;
        $qual->delete();

        $this->logAction($request, 'Delete Qualification', "Deleted qualification '{$name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Qualification deleted successfully!']);
    }

    // --- STATES ---
    public function getStates(): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        return response()->json(['status' => 'success', 'data' => State::orderBy('name')->get()]);
    }

    public function storeState(Request $request): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $request->validate([
            'name' => 'required|string|max:100|unique:states,name',
            'code' => 'required|string|max:20|unique:states,code'
        ]);

        $state = State::create([
            'name' => $request->name,
            'code' => strtoupper($request->code)
        ]);

        $this->logAction($request, 'Create State', "Created state '{$state->name}' ({$state->code})");
        return response()->json(['status' => 'success', 'message' => 'State created successfully!', 'data' => $state]);
    }

    public function updateState(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $state = State::findOrFail($id);
        $request->validate([
            'name' => "required|string|max:100|unique:states,name,{$id}",
            'code' => "required|string|max:20|unique:states,code,{$id}"
        ]);

        $state->update([
            'name' => $request->name,
            'code' => strtoupper($request->code)
        ]);

        $this->logAction($request, 'Update State', "Updated state to '{$state->name}' ({$state->code})");
        return response()->json(['status' => 'success', 'message' => 'State updated successfully!', 'data' => $state]);
    }

    public function deleteState(Request $request, int $id): JsonResponse
    {
        if ($err = $this->checkAdmin()) return $err;
        $state = State::findOrFail($id);

        if (\App\Models\JobPost::where('state_id', $id)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete State: It is linked to active recruitment posts.'], 400);
        }

        $name = $state->name;
        $state->delete();

        $this->logAction($request, 'Delete State', "Deleted state '{$name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'State deleted successfully!']);
    }
}

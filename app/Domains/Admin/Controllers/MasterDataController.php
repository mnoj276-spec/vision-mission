<?php

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\Services\Contracts\AdminServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\Qualification;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * MasterDataController — CRUD for Categories, Departments, Qualifications, States.
 * Moved from Frontend namespace. Authorization via EnsureAdmin middleware.
 * Duplicate logAction() now delegated to AdminService.
 */
class MasterDataController extends Controller
{
    public function __construct(protected AdminServiceInterface $adminService) {}

    // ─── CATEGORIES ─────────────────────────────────────────────────────────

    public function getCategories(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => Category::orderBy('name')->get()]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100|unique:categories,name']);
        $category = Category::create(['name' => $request->name, 'slug' => str()->slug($request->name)]);
        $this->log($request, 'Create Category', "Created '{$category->name}' (ID: {$category->id})");
        return response()->json(['status' => 'success', 'message' => 'Category created!', 'data' => $category]);
    }

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $request->validate(['name' => "required|string|max:100|unique:categories,name,{$id}"]);
        $category->update(['name' => $request->name, 'slug' => str()->slug($request->name)]);
        $this->log($request, 'Update Category', "Updated '{$category->name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Category updated!', 'data' => $category]);
    }

    public function deleteCategory(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        if (JobPost::where('category_id', $id)->exists())
            return response()->json(['status' => 'error', 'message' => 'Cannot delete: linked to active posts.'], 400);
        $name = $category->name; $category->delete();
        $this->log($request, 'Delete Category', "Deleted '{$name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Category deleted!']);
    }

    // ─── DEPARTMENTS ─────────────────────────────────────────────────────────

    public function getDepartments(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => Department::orderBy('name')->get()]);
    }

    public function storeDepartment(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:150|unique:departments,name', 'code' => 'required|string|max:20|unique:departments,code']);
        $dept = Department::create(['name' => $request->name, 'code' => strtoupper($request->code)]);
        $this->log($request, 'Create Department', "Created '{$dept->name}' ({$dept->code})");
        return response()->json(['status' => 'success', 'message' => 'Department created!', 'data' => $dept]);
    }

    public function updateDepartment(Request $request, int $id): JsonResponse
    {
        $dept = Department::findOrFail($id);
        $request->validate(['name' => "required|string|max:150|unique:departments,name,{$id}", 'code' => "required|string|max:20|unique:departments,code,{$id}"]);
        $dept->update(['name' => $request->name, 'code' => strtoupper($request->code)]);
        $this->log($request, 'Update Department', "Updated '{$dept->name}' ({$dept->code})");
        return response()->json(['status' => 'success', 'message' => 'Department updated!', 'data' => $dept]);
    }

    public function deleteDepartment(Request $request, int $id): JsonResponse
    {
        $dept = Department::findOrFail($id);
        if (JobPost::where('department_id', $id)->exists())
            return response()->json(['status' => 'error', 'message' => 'Cannot delete: linked to active posts.'], 400);
        $name = $dept->name; $dept->delete();
        $this->log($request, 'Delete Department', "Deleted '{$name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Department deleted!']);
    }

    // ─── QUALIFICATIONS ──────────────────────────────────────────────────────

    public function getQualifications(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => Qualification::orderBy('id')->get()]);
    }

    public function storeQualification(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100|unique:qualifications,name']);
        $qual = Qualification::create(['name' => $request->name, 'slug' => str()->slug($request->name)]);
        $this->log($request, 'Create Qualification', "Created '{$qual->name}'");
        return response()->json(['status' => 'success', 'message' => 'Qualification created!', 'data' => $qual]);
    }

    public function updateQualification(Request $request, int $id): JsonResponse
    {
        $qual = Qualification::findOrFail($id);
        $request->validate(['name' => "required|string|max:100|unique:qualifications,name,{$id}"]);
        $qual->update(['name' => $request->name, 'slug' => str()->slug($request->name)]);
        $this->log($request, 'Update Qualification', "Updated '{$qual->name}'");
        return response()->json(['status' => 'success', 'message' => 'Qualification updated!', 'data' => $qual]);
    }

    public function deleteQualification(Request $request, int $id): JsonResponse
    {
        $qual = Qualification::findOrFail($id);
        if (JobPost::where('qualification_id', $id)->exists())
            return response()->json(['status' => 'error', 'message' => 'Cannot delete: linked to active posts.'], 400);
        $name = $qual->name; $qual->delete();
        $this->log($request, 'Delete Qualification', "Deleted '{$name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Qualification deleted!']);
    }

    // ─── STATES ──────────────────────────────────────────────────────────────

    public function getStates(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => State::orderBy('name')->get()]);
    }

    public function storeState(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100|unique:states,name', 'code' => 'required|string|max:20|unique:states,code']);
        $state = State::create(['name' => $request->name, 'code' => strtoupper($request->code)]);
        $this->log($request, 'Create State', "Created '{$state->name}' ({$state->code})");
        return response()->json(['status' => 'success', 'message' => 'State created!', 'data' => $state]);
    }

    public function updateState(Request $request, int $id): JsonResponse
    {
        $state = State::findOrFail($id);
        $request->validate(['name' => "required|string|max:100|unique:states,name,{$id}", 'code' => "required|string|max:20|unique:states,code,{$id}"]);
        $state->update(['name' => $request->name, 'code' => strtoupper($request->code)]);
        $this->log($request, 'Update State', "Updated '{$state->name}' ({$state->code})");
        return response()->json(['status' => 'success', 'message' => 'State updated!', 'data' => $state]);
    }

    public function deleteState(Request $request, int $id): JsonResponse
    {
        $state = State::findOrFail($id);
        if (JobPost::where('state_id', $id)->exists())
            return response()->json(['status' => 'error', 'message' => 'Cannot delete: linked to active posts.'], 400);
        $name = $state->name; $state->delete();
        $this->log($request, 'Delete State', "Deleted '{$name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'State deleted!']);
    }

    // ─── Shared helper (replaces duplicated logAction in 2 old controllers) ──

    private function log(Request $request, string $action, string $details): void
    {
        $this->adminService->logAction(Auth::id(), $request->ip(), $request->userAgent() ?? 'N/A', $action, $details);
    }
}

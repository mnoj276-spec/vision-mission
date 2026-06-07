<?php

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\Services\Contracts\AdminServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdManagementController extends Controller
{
    public function __construct(protected AdminServiceInterface $adminService) {}

    public function index(): JsonResponse
    {
        $ads = Advertisement::all();
        return response()->json([
            'status' => 'success',
            'data' => $ads
        ]);
    }

    public function storeOrUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'slot_name' => 'required|string|max:100',
            'ad_code'   => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $ad = Advertisement::updateOrCreate(
            ['slot_name' => $request->slot_name],
            ['ad_code' => $request->ad_code, 'is_active' => $request->is_active]
        );

        if (function_exists('settings_clear_cache')) {
            settings_clear_cache();
        }

        $status = $ad->is_active ? 'active' : 'inactive';
        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Manage Advertisement Slot',
            "Updated ad slot '{$ad->slot_name}' to be {$status}"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisement slot configured successfully!',
            'data' => $ad
        ]);
    }

    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $ad = Advertisement::findOrFail($id);
        $ad->is_active = !$ad->is_active;
        $ad->save();

        if (function_exists('settings_clear_cache')) {
            settings_clear_cache();
        }

        $status = $ad->is_active ? 'active' : 'inactive';
        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Toggle Advertisement Slot',
            "Toggled ad slot '{$ad->slot_name}' status to {$status}"
        );

        return response()->json([
            'status' => 'success',
            'message' => "Advertisement slot '{$ad->slot_name}' has been marked {$status}!",
            'data' => $ad
        ]);
    }
}

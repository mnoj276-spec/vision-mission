<?php

namespace App\Domains\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * AdminUserController — admin user management.
 * Extracted from the fat AdminController::getUsersList and ::updateUser.
 * Authorization handled by EnsureAdmin middleware on the route.
 */
class AdminUserController extends Controller
{
    public function getUsersList(): JsonResponse
    {
        $users = User::orderBy('id', 'desc')->get()->map(fn ($u) => [
            'id'        => $u->id,
            'name'      => $u->name,
            'email'     => $u->email,
            'phone'     => $u->phone ?? 'N/A',
            'role'      => $u->role,
            'is_active' => $u->is_active,
        ]);
        return response()->json(['status' => 'success', 'data' => ['users' => $users]]);
    }

    public function updateUser(Request $request, int $userId): JsonResponse
    {
        $user = User::find($userId);
        if (!$user) return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);

        if ($user->id === Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'You cannot deactivate or alter your own administrator session!'], 400);
        }

        Validator::make($request->all(), ['role' => 'nullable|string|in:admin,candidate', 'is_active' => 'nullable|boolean'])->validate();

        if ($request->has('role'))      $user->role      = $request->role;
        if ($request->has('is_active')) $user->is_active = (bool) $request->is_active;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'User parameters successfully synchronized!']);
    }
}

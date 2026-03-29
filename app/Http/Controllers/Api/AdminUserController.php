<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin-only listing (demonstrates isAdmin middleware).
 */
class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'phone', 'role', 'created_at'])
            ->orderBy('name')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($users);
    }
}

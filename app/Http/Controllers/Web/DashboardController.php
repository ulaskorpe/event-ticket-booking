<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Ensures a Sanctum API token exists in session (e.g. legacy sessions without one).
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if (! $request->session()->has('api_token')) {
            $request->session()->put('api_token', $user->refreshWebDashboardToken());
        }

        $canManageEvents = in_array($user->role, [UserRole::Admin, UserRole::Organizer], true);

        return view('dashboard', [
            'apiToken' => $request->session()->get('api_token'),
            'apiBaseUrl' => rtrim(url('/api'), '/'),
            'canManageEvents' => $canManageEvents,
        ]);
    }
}

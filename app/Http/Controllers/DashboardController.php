<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $route = match ($request->user()?->role) {
            'reporter' => 'dashboard.reporter',
            'volunteer' => 'dashboard.volunteer',
            'coordinator' => 'dashboard.coordinator',
            default => abort(403),
        };

        return redirect()->route($route);
    }

    public function reporter(): View
    {
        return view('dashboard.reporter');
    }

    public function volunteer(): View
    {
        return view('dashboard.volunteer');
    }

    public function coordinator(): View
    {
        return view('dashboard.coordinator');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Report;
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
        $pendingReports = Report::query()
            ->where('status', 'pending')
            ->with(['reporter:user_id,name', 'address:address_id,city_regency,province'])
            ->orderByDesc('created_at')
            ->orderByDesc('report_id')
            ->paginate(10);

        return view('dashboard.volunteer', compact('pendingReports'));
    }

    public function coordinator(): View
    {
        return view('dashboard.coordinator');
    }
}

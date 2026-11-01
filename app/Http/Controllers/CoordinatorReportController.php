<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportForwardingRequest;
use App\Models\Report;
use App\Models\ReportForwarding;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoordinatorReportController extends Controller
{
    public function show(Report $report): View
    {
        Gate::authorize('viewAsCoordinator', $report);

        $report->load([
            'reporter:user_id,name,phone_number',
            'address:address_id,detail_address,city_regency,province,island',
            'reviews.volunteer:user_id,name',
            'forwardings.coordinator:user_id,name',
        ]);

        $relatedReports = Report::query()
            ->with(['reporter:user_id,name', 'address:address_id,city_regency,province'])
            ->where('report_id', '<>', $report->getKey())
            ->whereHas('address', function ($query) use ($report): void {
                $query->where('city_regency', $report->address->city_regency)
                    ->where('province', $report->address->province);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('report_id')
            ->limit(6)
            ->get();

        return view('coordinator.reports.show', compact('relatedReports', 'report'));
    }

    public function evidence(Report $report): StreamedResponse
    {
        Gate::authorize('viewAsCoordinator', $report);

        abort_if(
            $report->evidence_file === null
                || ! Storage::disk('public')->exists($report->evidence_file),
            404,
        );

        return Storage::disk('public')->response($report->evidence_file);
    }

    public function forwardings(Report $report): RedirectResponse
    {
        Gate::authorize('viewAsCoordinator', $report);

        return redirect()->route('coordinator.reports.show', $report);
    }

    public function storeForwarding(StoreReportForwardingRequest $request, Report $report): RedirectResponse
    {
        $validated = $request->validated();

        /** @var User $coordinator */
        $coordinator = $request->user();

        DB::transaction(function () use ($coordinator, $report, $validated): void {
            $lockedReport = Report::query()
                ->whereKey($report->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('forwardAsCoordinator', $lockedReport);

            ReportForwarding::create([
                'report_id' => $lockedReport->report_id,
                'coordinator_id' => $coordinator->getAuthIdentifier(),
                'destination_name' => $validated['destination_name'],
                'forwarding_note' => $validated['forwarding_note'] ?? null,
                'forwarded_at' => now(),
            ]);

            $lockedReport->status = 'forwarded';
            $lockedReport->save();
        });

        return redirect()
            ->route('coordinator.reports.show', $report)
            ->with('status', 'Penerusan laporan berhasil dicatat.');
    }

    public function complete(Report $report): RedirectResponse
    {
        DB::transaction(function () use ($report): void {
            $lockedReport = Report::query()
                ->whereKey($report->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('completeAsCoordinator', $lockedReport);

            $lockedReport->status = 'completed';
            $lockedReport->save();
        });

        return redirect()
            ->route('coordinator.reports.show', $report)
            ->with('status', 'Perkembangan laporan diperbarui menjadi selesai.');
    }
}

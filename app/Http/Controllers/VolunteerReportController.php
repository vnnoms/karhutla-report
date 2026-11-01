<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportReviewRequest;
use App\Models\Report;
use App\Models\ReportReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VolunteerReportController extends Controller
{
    public function show(Report $report): View
    {
        Gate::authorize('viewAsVolunteer', $report);
        $report->load([
            'reporter:user_id,name,phone_number',
            'address:address_id,detail_address,city_regency,province,island',
            'reviews.volunteer:user_id,name',
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

        return view('volunteer.reports.show', compact('relatedReports', 'report'));
    }

    public function storeReview(StoreReportReviewRequest $request, Report $report): RedirectResponse
    {
        $validated = $request->validated();

        /** @var User $volunteer */
        $volunteer = $request->user();

        DB::transaction(function () use ($report, $validated, $volunteer): void {
            $lockedReport = Report::query()
                ->whereKey($report->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('reviewAsVolunteer', $lockedReport);

            ReportReview::create([
                'report_id' => $lockedReport->report_id,
                'volunteer_id' => $volunteer->getAuthIdentifier(),
                'review_note' => $validated['review_note'],
                'review_date' => now(),
                'followed_up_required' => (bool) ($validated['followed_up_required'] ?? false),
            ]);

            $lockedReport->status = 'reviewed';
            $lockedReport->save();
        });

        return redirect()
            ->route('volunteer.reports.show', $report)
            ->with('status', 'Pemeriksaan laporan berhasil disimpan.');
    }

    public function evidence(Report $report): StreamedResponse
    {
        Gate::authorize('viewAsVolunteer', $report);

        abort_if(
            $report->evidence_file === null
                || ! Storage::disk('public')->exists($report->evidence_file),
            404,
        );

        return Storage::disk('public')->response($report->evidence_file);
    }
}

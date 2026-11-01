<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Address;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Report::class);

        /** @var User $user */
        $user = $request->user();

        $reports = $user->reports()
            ->with('address')
            ->latest('created_at')
            ->paginate(10);

        return view('reporter.reports.index', compact('reports'));
    }

    public function create(): View
    {
        Gate::authorize('create', Report::class);

        return view('reporter.reports.create', ['report' => new Report]);
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        Gate::authorize('create', Report::class);

        $validated = $request->validated();
        $evidencePath = $this->storeEvidence($request->file('evidence_file'));
        $addressData = $this->addressData($validated);
        $user = $request->user();

        try {
            $report = DB::transaction(function () use ($addressData, $evidencePath, $user, $validated): Report {
                $address = Address::create($addressData);

                return $user->reports()->create([
                    'evidence_file' => $evidencePath,
                    'report_description' => $validated['report_description'],
                    'incident_time' => Carbon::parse($validated['incident_time'])->format('Y-m-d H:i:s'),
                    'condition_level' => $validated['condition_level'],
                    'address_id' => $address->address_id,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'status' => 'pending',
                ]);
            });
        } catch (Throwable $exception) {
            $this->deleteEvidence($evidencePath);

            throw $exception;
        }

        return redirect()
            ->route('reporter.reports.show', $report)
            ->with('status', 'Laporan berhasil dikirim dan menunggu pemeriksaan.');
    }

    public function show(Report $report): View
    {
        Gate::authorize('view', $report);
        $report->load('address');

        return view('reporter.reports.show', compact('report'));
    }

    public function edit(Report $report): View
    {
        Gate::authorize('update', $report);
        $report->load('address');

        return view('reporter.reports.edit', compact('report'));
    }

    public function update(UpdateReportRequest $request, Report $report): RedirectResponse
    {
        Gate::authorize('update', $report);

        $validated = $request->validated();
        $newEvidencePath = $this->storeEvidence($request->file('evidence_file'));
        $oldEvidencePath = $report->evidence_file;
        $addressData = $this->addressData($validated);

        try {
            DB::transaction(function () use ($addressData, $newEvidencePath, $report, $validated): void {
                $address = $report->address;
                $addressIsShared = $address !== null
                    && $address->reports()
                        ->where('report_id', '<>', $report->getKey())
                        ->exists();

                if ($address === null || $addressIsShared) {
                    $address = Address::create($addressData);
                } else {
                    $address->update($addressData);
                }

                $report->fill([
                    'report_description' => $validated['report_description'],
                    'incident_time' => Carbon::parse($validated['incident_time'])->format('Y-m-d H:i:s'),
                    'condition_level' => $validated['condition_level'],
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                ]);
                $report->address_id = $address->address_id;

                if ($newEvidencePath !== null) {
                    $report->evidence_file = $newEvidencePath;
                }

                $report->save();
            });
        } catch (Throwable $exception) {
            $this->deleteEvidence($newEvidencePath);

            throw $exception;
        }

        if ($newEvidencePath !== null && $oldEvidencePath !== null) {
            $this->deleteEvidence($oldEvidencePath);
        }

        return redirect()
            ->route('reporter.reports.show', $report)
            ->with('status', 'Laporan berhasil diperbarui.');
    }

    public function destroy(Report $report): RedirectResponse
    {
        Gate::authorize('delete', $report);
        $evidencePath = $report->evidence_file;
        $addressId = $report->address_id;

        DB::transaction(function () use ($addressId, $report): void {
            $report->delete();

            $addressIsUnused = ! Report::query()
                ->where('address_id', $addressId)
                ->exists();

            if ($addressIsUnused) {
                Address::query()->whereKey($addressId)->delete();
            }
        });
        $this->deleteEvidence($evidencePath);

        return redirect()
            ->route('reporter.reports.index')
            ->with('status', 'Laporan berhasil dihapus.');
    }

    public function evidence(Report $report): StreamedResponse
    {
        Gate::authorize('view', $report);

        abort_if(
            $report->evidence_file === null
                || ! Storage::disk('public')->exists($report->evidence_file),
            404,
        );

        return Storage::disk('public')->response($report->evidence_file);
    }

    private function addressData(array $validated): array
    {
        return [
            'detail_address' => $validated['detail_address'],
            'city_regency' => $validated['city_regency'],
            'province' => $validated['province'],
            'island' => $validated['island'],
        ];
    }

    private function storeEvidence(?UploadedFile $file): ?string
    {
        if ($file === null) {
            return null;
        }

        $path = $file->store('reports/evidence', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('The report evidence could not be stored.');
        }

        return $path;
    }

    private function deleteEvidence(?string $path): void
    {
        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }
}

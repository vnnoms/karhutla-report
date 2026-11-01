<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Report;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportRecapController extends Controller
{
    public function index(Request $request): View
    {
        $provinces = Address::query()
            ->whereHas('reports')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        $statuses = Report::query()
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $cityOptions = Address::query()
            ->whereHas('reports')
            ->when(
                is_string($request->input('province')) && $request->input('province') !== '',
                fn ($query) => $query->where('province', $request->input('province')),
            )
            ->distinct()
            ->orderBy('city_regency')
            ->pluck('city_regency');

        $filters = $request->validate([
            'province' => ['nullable', 'string', Rule::in($provinces->all())],
            'city_regency' => ['nullable', 'string', Rule::in($cityOptions->all())],
            'status' => ['nullable', 'string', Rule::in($statuses->all())],
        ]);

        $summary = Report::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COALESCE(SUM(status = ?), 0) AS pending', ['pending'])
            ->selectRaw('COALESCE(SUM(status = ?), 0) AS reviewed', ['reviewed'])
            ->selectRaw('COALESCE(SUM(status = ?), 0) AS forwarded', ['forwarded'])
            ->selectRaw('COALESCE(SUM(status = ?), 0) AS completed', ['completed'])
            ->first();

        $regionQuery = DB::table('reports')
            ->join('addresses', 'addresses.address_id', '=', 'reports.address_id')
            ->when($filters['province'] ?? null, fn (Builder $query, string $province) => $query->where('addresses.province', $province))
            ->when($filters['city_regency'] ?? null, fn (Builder $query, string $city) => $query->where('addresses.city_regency', $city))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('reports.status', $status));

        $reportsByProvince = (clone $regionQuery)
            ->select('addresses.province')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('addresses.province')
            ->orderByDesc('total')
            ->orderBy('addresses.province')
            ->get();

        $reportsByCity = (clone $regionQuery)
            ->select('addresses.province', 'addresses.city_regency')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('addresses.province', 'addresses.city_regency')
            ->orderByDesc('total')
            ->orderBy('addresses.province')
            ->orderBy('addresses.city_regency')
            ->get();

        $reports = Report::query()
            ->with(['reporter:user_id,name', 'address:address_id,city_regency,province'])
            ->withCount(['reviews as follow_up_reviews_count' => fn ($query) => $query->where('followed_up_required', true)])
            ->when($filters['province'] ?? null, fn ($query, string $province) => $query->whereHas('address', fn ($addressQuery) => $addressQuery->where('province', $province)))
            ->when($filters['city_regency'] ?? null, fn ($query, string $city) => $query->whereHas('address', fn ($addressQuery) => $addressQuery->where('city_regency', $city)))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('report_id')
            ->paginate(15)
            ->withQueryString();

        $statusLabels = [
            'pending' => 'Menunggu',
            'reviewed' => 'Ditinjau',
            'forwarded' => 'Diteruskan',
            'completed' => 'Selesai',
            'approved' => 'Disetujui',
            'processed' => 'Diproses',
            'rejected' => 'Ditolak',
        ];
        $conditionLabels = [
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'critical' => 'Kritis',
        ];

        return view('coordinator.reports.recap', compact(
            'cityOptions',
            'conditionLabels',
            'filters',
            'provinces',
            'reports',
            'reportsByCity',
            'reportsByProvince',
            'statusLabels',
            'statuses',
            'summary',
        ));
    }
}

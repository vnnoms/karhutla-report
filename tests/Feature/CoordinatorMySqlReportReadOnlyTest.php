<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoordinatorMySqlReportReadOnlyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('KARHUTLA_MYSQL_READONLY') !== '1') {
            $this->markTestSkipped('Set KARHUTLA_MYSQL_READONLY=1 to run this read-only MySQL smoke test.');
        }

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'db_karhutla',
        ]);
        DB::purge('mysql');
        $this->withoutVite();
    }

    public function test_mysql_review_follow_up_reports_open_in_coordinator_detail_with_forwarding_form(): void
    {
        $coordinator = User::query()->where('role', 'coordinator')->firstOrFail();
        $reports = Report::query()
            ->where('status', 'reviewed')
            ->whereHas('reviews', fn ($query) => $query->where('followed_up_required', true))
            ->orderBy('report_id')
            ->limit(2)
            ->get();

        $this->assertCount(2, $reports, 'The live database should contain the two reports marked for coordinator follow-up.');

        foreach ($reports as $report) {
            $this->actingAs($coordinator)
                ->get(route('coordinator.reports.show', $report))
                ->assertOk()
                ->assertSee('Teruskan laporan')
                ->assertSee('Tujuan penerusan')
                ->assertSee('Direkomendasikan untuk diteruskan');
        }
    }

    public function test_existing_mysql_forwarding_history_renders_and_forwardings_url_redirects_to_detail(): void
    {
        $coordinator = User::query()->where('role', 'coordinator')->firstOrFail();
        $report = Report::query()
            ->where('status', 'forwarded')
            ->whereHas('forwardings')
            ->orderByDesc('report_id')
            ->firstOrFail();
        $forwarding = $report->forwardings()->firstOrFail();

        $this->actingAs($coordinator)
            ->get(route('coordinator.reports.show', $report))
            ->assertOk()
            ->assertSee($forwarding->destination_name)
            ->assertSee('Tandai selesai');

        $this->get(route('coordinator.reports.forwardings.index', $report))
            ->assertRedirect(route('coordinator.reports.show', $report));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Report;
use App\Models\ReportForwarding;
use App\Models\ReportReview;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoordinatorReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        $this->withoutVite();

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->bigIncrements('user_id');
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('phone_number', 20)->nullable();
            $table->string('role', 20);
        });

        Schema::connection('sqlite')->create('addresses', function (Blueprint $table): void {
            $table->bigIncrements('address_id');
            $table->text('detail_address');
            $table->string('city_regency', 100);
            $table->string('province', 100);
            $table->string('island', 100);
        });

        Schema::connection('sqlite')->create('reports', function (Blueprint $table): void {
            $table->bigIncrements('report_id');
            $table->unsignedBigInteger('reporter_id');
            $table->string('evidence_file')->nullable();
            $table->text('report_description');
            $table->dateTime('incident_time');
            $table->string('condition_level', 50);
            $table->unsignedBigInteger('address_id');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 30);
            $table->timestamps();
            $table->foreign('reporter_id')->references('user_id')->on('users');
            $table->foreign('address_id')->references('address_id')->on('addresses');
        });

        Schema::connection('sqlite')->create('report_reviews', function (Blueprint $table): void {
            $table->bigIncrements('review_id');
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('volunteer_id');
            $table->text('review_note')->nullable();
            $table->dateTime('review_date')->useCurrent();
            $table->boolean('followed_up_required')->nullable();
            $table->foreign('report_id')->references('report_id')->on('reports');
            $table->foreign('volunteer_id')->references('user_id')->on('users');
        });

        Schema::connection('sqlite')->create('report_forwardings', function (Blueprint $table): void {
            $table->bigIncrements('forwarding_id');
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('coordinator_id');
            $table->string('destination_name', 150);
            $table->text('forwarding_note')->nullable();
            $table->dateTime('forwarded_at')->useCurrent();
            $table->foreign('report_id')->references('report_id')->on('reports');
            $table->foreign('coordinator_id')->references('user_id')->on('users');
        });
    }

    public function test_coordinator_can_view_report_details_reviews_related_reports_and_evidence(): void
    {
        Storage::fake('public');
        $coordinator = $this->createUser('coordinator', 'coordinator@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $volunteer = $this->createUser('volunteer', 'volunteer@example.test');
        $path = 'reports/evidence/coordinator-test.jpg';
        Storage::disk('public')->put($path, 'test evidence');
        $report = $this->createReport($reporter, 'Deskripsi lengkap untuk coordinator.', 'reviewed', 'Samarinda', 'Kalimantan Timur', $path);
        $related = $this->createReport($reporter, 'Laporan terkait di kota yang sama.');
        $this->createReport($reporter, 'Laporan dari provinsi lain.', 'pending', 'Bandung', 'Jawa Barat');
        ReportReview::create([
            'report_id' => $report->report_id,
            'volunteer_id' => $volunteer->user_id,
            'review_note' => 'Informasi lokasi perlu diteruskan.',
            'review_date' => now(),
            'followed_up_required' => true,
        ]);

        $this->actingAs($coordinator)
            ->get(route('coordinator.reports.show', $report))
            ->assertOk()
            ->assertSee('Deskripsi lengkap untuk coordinator.')
            ->assertSee('Informasi lokasi perlu diteruskan.')
            ->assertSee('Laporan terkait di kota yang sama.')
            ->assertDontSee('Laporan dari provinsi lain.')
            ->assertSee(route('coordinator.reports.evidence', $report))
            ->assertSee(route('coordinator.reports.show', $related));

        $this->get(route('coordinator.reports.evidence', $report))->assertOk();
    }

    public function test_coordinator_can_forward_a_reviewed_report_and_records_their_identity(): void
    {
        $coordinator = $this->createUser('coordinator', 'coordinator@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $report = $this->createReport($reporter, 'Laporan sudah ditinjau.', 'reviewed');

        $this->actingAs($coordinator)
            ->post(route('coordinator.reports.forwardings.store', $report), [
                'destination_name' => 'BPBD Kabupaten Samarinda',
                'forwarding_note' => 'Mohon verifikasi lapangan secepatnya.',
            ])
            ->assertRedirect(route('coordinator.reports.show', $report));

        $this->assertDatabaseHas('report_forwardings', [
            'report_id' => $report->report_id,
            'coordinator_id' => $coordinator->user_id,
            'destination_name' => 'BPBD Kabupaten Samarinda',
            'forwarding_note' => 'Mohon verifikasi lapangan secepatnya.',
        ]);
        $this->assertDatabaseHas('reports', ['report_id' => $report->report_id, 'status' => 'forwarded']);
    }

    public function test_coordinator_can_complete_forwarded_reports_but_cannot_skip_review_or_forwarding(): void
    {
        $coordinator = $this->createUser('coordinator', 'coordinator@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $pendingReport = $this->createReport($reporter, 'Laporan masih menunggu.');

        $this->actingAs($coordinator)
            ->post(route('coordinator.reports.forwardings.store', $pendingReport), [
                'destination_name' => 'BPBD Samarinda',
            ])
            ->assertForbidden();
        $this->post(route('coordinator.reports.complete', $pendingReport))->assertForbidden();

        $forwardedReport = $this->createReport($reporter, 'Laporan yang sudah diteruskan.', 'forwarded');
        ReportForwarding::create([
            'report_id' => $forwardedReport->report_id,
            'coordinator_id' => $coordinator->user_id,
            'destination_name' => 'BPBD Samarinda',
            'forwarding_note' => null,
            'forwarded_at' => now(),
        ]);

        $this->post(route('coordinator.reports.complete', $forwardedReport))
            ->assertRedirect(route('coordinator.reports.show', $forwardedReport));
        $this->assertDatabaseHas('reports', ['report_id' => $forwardedReport->report_id, 'status' => 'completed']);
    }

    public function test_only_coordinators_can_access_report_details_and_forwarding_validation_is_enforced(): void
    {
        $coordinator = $this->createUser('coordinator', 'coordinator@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $volunteer = $this->createUser('volunteer', 'volunteer@example.test');
        $reviewedReport = $this->createReport($reporter, 'Laporan ditinjau.', 'reviewed');

        $this->actingAs($volunteer)
            ->get(route('coordinator.reports.show', $reviewedReport))
            ->assertForbidden();
        $this->actingAs($reporter)
            ->get(route('coordinator.reports.show', $reviewedReport))
            ->assertForbidden();

        $this->actingAs($coordinator)
            ->post(route('coordinator.reports.forwardings.store', $reviewedReport), ['destination_name' => ''])
            ->assertSessionHasErrors('destination_name');

        $this->assertDatabaseCount('report_forwardings', 0);
        $this->assertDatabaseHas('reports', ['report_id' => $reviewedReport->report_id, 'status' => 'reviewed']);
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => 'Test '.ucfirst($role),
            'email' => $email,
            'password' => Hash::make('HutanAman123!'),
            'phone_number' => '081234567890',
            'role' => $role,
        ]);
    }

    private function createReport(
        User $reporter,
        string $description,
        string $status = 'pending',
        string $city = 'Samarinda',
        string $province = 'Kalimantan Timur',
        ?string $evidencePath = null,
    ): Report {
        $address = Address::create([
            'detail_address' => 'Jalan Hutan, dekat pos pemantauan.',
            'city_regency' => $city,
            'province' => $province,
            'island' => 'Kalimantan',
        ]);

        return $reporter->reports()->create([
            'evidence_file' => $evidencePath,
            'report_description' => $description,
            'incident_time' => now(),
            'condition_level' => 'high',
            'address_id' => $address->address_id,
            'status' => $status,
        ]);
    }
}

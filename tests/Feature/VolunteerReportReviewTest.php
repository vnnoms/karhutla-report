<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VolunteerReportReviewTest extends TestCase
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
    }

    public function test_volunteer_can_see_report_details_and_related_reports_in_the_same_area(): void
    {
        $volunteer = $this->createUser('volunteer', 'volunteer@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $report = $this->createReport($reporter, 'Titik api dekat kawasan hutan.');
        $this->createReport($reporter, 'Asap terlihat di area yang sama.');
        $this->createReport($reporter, 'Laporan dari wilayah lain.', 'pending', 'Bandung', 'Jawa Barat');

        $this->actingAs($volunteer)
            ->get(route('volunteer.reports.show', $report))
            ->assertOk()
            ->assertSee('Titik api dekat kawasan hutan.')
            ->assertSee('Jalan Hutan, dekat pos pemantauan.')
            ->assertSee('Reporter Test')
            ->assertSee('Asap terlihat di area yang sama.')
            ->assertDontSee('Laporan dari wilayah lain.')
            ->assertSee('Simpan hasil pemeriksaan');
    }

    public function test_volunteer_can_open_private_evidence_for_review(): void
    {
        Storage::fake('public');
        $volunteer = $this->createUser('volunteer', 'volunteer@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $evidencePath = 'reports/evidence/review-photo.jpg';
        Storage::disk('public')->put($evidencePath, 'temporary test photo');
        $report = $this->createReport($reporter, 'Laporan dengan foto bukti.', 'pending', 'Samarinda', 'Kalimantan Timur', $evidencePath);

        $this->actingAs($volunteer)
            ->get(route('volunteer.reports.show', $report))
            ->assertOk()
            ->assertSee(route('volunteer.reports.evidence', $report));

        $this->get(route('volunteer.reports.evidence', $report))->assertOk();
    }

    public function test_volunteer_review_saves_note_flag_and_updates_report_status(): void
    {
        $volunteer = $this->createUser('volunteer', 'volunteer@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $report = $this->createReport($reporter, 'Laporan menunggu pemeriksaan.');

        $this->actingAs($volunteer)
            ->post(route('volunteer.reports.reviews.store', $report), [
                'review_note' => 'Lokasi dan waktu kejadian sudah jelas, perlu verifikasi lapangan.',
                'followed_up_required' => '1',
            ])
            ->assertRedirect(route('volunteer.reports.show', $report));

        $this->assertDatabaseHas('report_reviews', [
            'report_id' => $report->report_id,
            'volunteer_id' => $volunteer->user_id,
            'review_note' => 'Lokasi dan waktu kejadian sudah jelas, perlu verifikasi lapangan.',
            'followed_up_required' => 1,
        ]);
        $this->assertDatabaseHas('reports', [
            'report_id' => $report->report_id,
            'status' => 'reviewed',
        ]);

        $this->get(route('volunteer.reports.show', $report))
            ->assertOk()
            ->assertSee('Lokasi dan waktu kejadian sudah jelas')
            ->assertSee('Perlu diteruskan ke koordinator');

        $this->post(route('volunteer.reports.reviews.store', $report), [
            'review_note' => 'Tidak boleh mengirim review kedua untuk laporan yang sudah ditinjau.',
        ])->assertForbidden();
    }

    public function test_review_note_is_required_and_only_volunteers_can_access_review_actions(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $volunteer = $this->createUser('volunteer', 'volunteer@example.test');
        $report = $this->createReport($reporter, 'Laporan yang menunggu.');

        $this->actingAs($volunteer)
            ->post(route('volunteer.reports.reviews.store', $report), ['review_note' => ''])
            ->assertSessionHasErrors('review_note');

        $this->actingAs($reporter)
            ->get(route('volunteer.reports.show', $report))
            ->assertForbidden();

        $this->post(route('volunteer.reports.reviews.store', $report), [
            'review_note' => 'Akses reporter harus ditolak.',
        ])->assertForbidden();

        $this->assertDatabaseHas('reports', ['report_id' => $report->report_id, 'status' => 'pending']);
        $this->assertDatabaseCount('report_reviews', 0);
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => 'Reporter Test',
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
            'incident_time' => '2026-11-01 14:30:00',
            'condition_level' => 'high',
            'address_id' => $address->address_id,
            'latitude' => '-0.5022000',
            'longitude' => '117.1537000',
            'status' => $status,
        ]);
    }
}

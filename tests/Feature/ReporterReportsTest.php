<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReporterReportsTest extends TestCase
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
        Storage::fake('public');

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
        });

        Schema::connection('sqlite')->create('report_forwardings', function (Blueprint $table): void {
            $table->bigIncrements('forwarding_id');
            $table->unsignedBigInteger('report_id');
        });
    }

    public function test_reporter_sees_only_their_own_reports(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $otherReporter = $this->createUser('reporter', 'other@example.test');
        $this->createReport($reporter, 'Laporan milik saya');
        $this->createReport($otherReporter, 'Laporan privat orang lain');

        $this->actingAs($reporter)
            ->get(route('reporter.reports.index'))
            ->assertOk()
            ->assertSee('Laporan milik saya')
            ->assertDontSee('Laporan privat orang lain');
    }

    public function test_non_reporter_cannot_open_reporter_report_pages(): void
    {
        $volunteer = $this->createUser('volunteer', 'volunteer@example.test');

        $this->actingAs($volunteer)
            ->get(route('reporter.reports.index'))
            ->assertForbidden();
    }

    public function test_report_submission_creates_address_and_pending_report_for_authenticated_reporter(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');

        $response = $this->actingAs($reporter)->post(route('reporter.reports.store'), $this->validReport([
            'evidence_file' => UploadedFile::fake()->createWithContent('location.jpg', 'test image'),
            'reporter_id' => 9999,
            'status' => 'approved',
        ]));

        $report = Report::query()->firstOrFail();

        $response->assertRedirect(route('reporter.reports.show', $report));
        $this->assertDatabaseHas('addresses', [
            'address_id' => $report->address_id,
            'city_regency' => 'Samarinda',
            'province' => 'Kalimantan Timur',
        ]);
        $this->assertDatabaseHas('reports', [
            'report_id' => $report->report_id,
            'reporter_id' => $reporter->user_id,
            'status' => 'pending',
            'condition_level' => 'high',
        ]);
        Storage::disk('public')->assertExists($report->evidence_file);
    }

    public function test_invalid_report_data_is_rejected_without_creating_records(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');

        $this->actingAs($reporter)
            ->post(route('reporter.reports.store'), [
                'report_description' => '',
                'incident_time' => 'not-a-date',
                'condition_level' => 'extreme',
                'latitude' => 120,
                'longitude' => -200,
                'evidence_file' => UploadedFile::fake()->createWithContent('evidence.txt', 'not an image'),
            ])
            ->assertSessionHasErrors([
                'report_description',
                'incident_time',
                'condition_level',
                'detail_address',
                'city_regency',
                'province',
                'island',
                'latitude',
                'longitude',
                'evidence_file',
            ]);

        $this->assertDatabaseCount('addresses', 0);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_detail_is_forbidden_to_another_reporter(): void
    {
        $owner = $this->createUser('reporter', 'owner@example.test');
        $otherReporter = $this->createUser('reporter', 'other@example.test');
        $report = $this->createReport($owner, 'Laporan privat');

        $this->actingAs($otherReporter)
            ->get(route('reporter.reports.show', $report))
            ->assertForbidden();
    }

    public function test_reporter_can_open_create_edit_and_detail_pages(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $report = $this->createReport($reporter, 'Laporan untuk pemeriksaan halaman.');

        $this->actingAs($reporter)
            ->get(route('reporter.reports.create'))
            ->assertOk()
            ->assertSee('Buat laporan kejadian');

        $this->get(route('reporter.reports.edit', $report))
            ->assertOk()
            ->assertSee('Edit laporan');

        $this->get(route('reporter.reports.show', $report))
            ->assertOk()
            ->assertSee('Laporan untuk pemeriksaan halaman.');
    }

    public function test_reporter_can_edit_their_pending_report_and_address(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $report = $this->createReport($reporter, 'Deskripsi awal');

        $this->actingAs($reporter)
            ->put(route('reporter.reports.update', $report), $this->validReport([
                'report_description' => 'Asap terlihat dari sisi timur kawasan.',
                'city_regency' => 'Balikpapan',
            ]))
            ->assertRedirect(route('reporter.reports.show', $report));

        $this->assertDatabaseHas('reports', [
            'report_id' => $report->report_id,
            'reporter_id' => $reporter->user_id,
            'report_description' => 'Asap terlihat dari sisi timur kawasan.',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('addresses', [
            'address_id' => $report->address_id,
            'city_regency' => 'Balikpapan',
        ]);
    }

    public function test_reporter_can_replace_evidence_and_old_public_file_is_removed(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $oldPath = 'reports/evidence/old-photo.jpg';
        Storage::disk('public')->put($oldPath, 'old photo');
        $report = $this->createReport($reporter, 'Laporan awal', 'pending', $oldPath);

        $this->actingAs($reporter)
            ->put(route('reporter.reports.update', $report), $this->validReport([
                'evidence_file' => UploadedFile::fake()->createWithContent('new-photo.jpg', 'new photo'),
            ]))
            ->assertRedirect(route('reporter.reports.show', $report));

        $report->refresh();
        $this->assertNotSame($oldPath, $report->evidence_file);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($report->evidence_file);
    }

    public function test_evidence_must_be_jpg_jpeg_or_png_and_no_larger_than_two_megabytes(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');

        foreach ([
            UploadedFile::fake()->createWithContent('evidence.webp', 'not accepted'),
            UploadedFile::fake()->create('large.jpg', 2049, 'image/jpeg'),
        ] as $file) {
            $this->actingAs($reporter)
                ->post(route('reporter.reports.store'), $this->validReport(['evidence_file' => $file]))
                ->assertSessionHasErrors('evidence_file');
        }

        $this->assertDatabaseCount('addresses', 0);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_reporter_cannot_edit_a_processed_report(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $report = $this->createReport($reporter, 'Deskripsi awal', 'processed');

        $this->actingAs($reporter)
            ->put(route('reporter.reports.update', $report), $this->validReport([
                'report_description' => 'Perubahan yang tidak boleh tersimpan.',
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('reports', [
            'report_id' => $report->report_id,
            'report_description' => 'Deskripsi awal',
            'status' => 'processed',
        ]);
    }

    public function test_reporter_can_delete_their_pending_report_and_private_evidence(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $evidencePath = 'reports/evidence/sample.jpg';
        Storage::disk('public')->put($evidencePath, 'test image');
        $report = $this->createReport($reporter, 'Laporan yang dihapus', 'pending', $evidencePath);
        $addressId = $report->address_id;

        $this->actingAs($reporter)
            ->delete(route('reporter.reports.destroy', $report))
            ->assertRedirect(route('reporter.reports.index'));

        $this->assertDatabaseMissing('reports', ['report_id' => $report->report_id]);
        $this->assertDatabaseMissing('addresses', ['address_id' => $addressId]);
        Storage::disk('public')->assertMissing($evidencePath);
    }

    public function test_reporter_cannot_delete_a_processed_or_another_users_report(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $otherReporter = $this->createUser('reporter', 'other@example.test');
        $processedReport = $this->createReport($reporter, 'Processed', 'processed');
        $otherReport = $this->createReport($otherReporter, 'Milik reporter lain');

        $this->actingAs($reporter)
            ->delete(route('reporter.reports.destroy', $processedReport))
            ->assertForbidden();

        $this->delete(route('reporter.reports.destroy', $otherReport))
            ->assertForbidden();

        $this->assertDatabaseCount('reports', 2);
    }

    public function test_reporter_can_download_only_their_own_private_evidence(): void
    {
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $otherReporter = $this->createUser('reporter', 'other@example.test');
        $evidencePath = 'reports/evidence/private.jpg';
        Storage::disk('public')->put($evidencePath, 'test image');
        $report = $this->createReport($reporter, 'Laporan dengan foto', 'pending', $evidencePath);

        $this->actingAs($reporter)
            ->get(route('reporter.reports.evidence', $report))
            ->assertOk();

        $this->actingAs($otherReporter)
            ->get(route('reporter.reports.evidence', $report))
            ->assertForbidden();
    }

    /** @return array<string, mixed> */
    private function validReport(array $overrides = []): array
    {
        return array_merge([
            'report_description' => 'Titik api terlihat di dekat area hutan.',
            'incident_time' => '2026-10-30T14:30',
            'condition_level' => 'high',
            'detail_address' => 'Jalan Hutan, dekat pos pemantauan.',
            'city_regency' => 'Samarinda',
            'province' => 'Kalimantan Timur',
            'island' => 'Kalimantan',
            'latitude' => '-0.5022',
            'longitude' => '117.1537',
        ], $overrides);
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
        ?string $evidencePath = null,
    ): Report {
        $address = Address::create([
            'detail_address' => 'Jalan Hutan, dekat pos pemantauan.',
            'city_regency' => 'Samarinda',
            'province' => 'Kalimantan Timur',
            'island' => 'Kalimantan',
        ]);

        return $reporter->reports()->create([
            'evidence_file' => $evidencePath,
            'report_description' => $description,
            'incident_time' => '2026-10-30 14:30:00',
            'condition_level' => 'high',
            'address_id' => $address->address_id,
            'latitude' => '-0.5022',
            'longitude' => '117.1537',
            'status' => $status,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportRecapTest extends TestCase
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
    }

    public function test_coordinator_sees_dynamic_summary_regions_and_report_details(): void
    {
        $coordinator = $this->createUser('coordinator', 'coordinator@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $this->createReport($reporter, 'Laporan menunggu', 'pending', 'Samarinda', 'Kalimantan Timur');
        $this->createReport($reporter, 'Laporan ditinjau', 'reviewed', 'Balikpapan', 'Kalimantan Timur');
        $this->createReport($reporter, 'Laporan diteruskan', 'forwarded', 'Bandung', 'Jawa Barat');
        $this->createReport($reporter, 'Laporan selesai', 'completed', 'Bandung', 'Jawa Barat');

        $this->actingAs($coordinator)
            ->get(route('coordinator.reports.recap'))
            ->assertOk()
            ->assertViewHas('summary', function ($summary): bool {
                return (int) $summary->total === 4
                    && (int) $summary->pending === 1
                    && (int) $summary->reviewed === 1
                    && (int) $summary->forwarded === 1
                    && (int) $summary->completed === 1;
            })
            ->assertSee('Laporan menunggu')
            ->assertSee('Laporan selesai')
            ->assertSee('Kalimantan Timur')
            ->assertSee('Bandung');
    }

    public function test_region_and_status_filters_query_the_report_data(): void
    {
        $coordinator = $this->createUser('coordinator', 'coordinator@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $this->createReport($reporter, 'Sesuai filter', 'reviewed', 'Samarinda', 'Kalimantan Timur');
        $this->createReport($reporter, 'Status berbeda', 'pending', 'Samarinda', 'Kalimantan Timur');
        $this->createReport($reporter, 'Kota berbeda', 'reviewed', 'Bandung', 'Jawa Barat');

        $this->actingAs($coordinator)
            ->get(route('coordinator.reports.recap', [
                'province' => 'Kalimantan Timur',
                'city_regency' => 'Samarinda',
                'status' => 'reviewed',
            ]))
            ->assertOk()
            ->assertSee('Sesuai filter')
            ->assertDontSee('Status berbeda')
            ->assertDontSee('Kota berbeda')
            ->assertSee('Samarinda');
    }

    public function test_recap_paginates_and_preserves_active_filters(): void
    {
        $coordinator = $this->createUser('coordinator', 'coordinator@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');

        for ($index = 1; $index <= 17; $index++) {
            $this->createReport($reporter, 'Laporan urutan '.$index, 'pending', 'Samarinda', 'Kalimantan Timur');
        }

        $this->actingAs($coordinator)
            ->get(route('coordinator.reports.recap', ['province' => 'Kalimantan Timur']))
            ->assertOk()
            ->assertViewHas('reports', function ($reports): bool {
                return $reports->total() === 17 && $reports->count() === 15;
            })
            ->assertSee('page=2')
            ->assertSee('province=Kalimantan%20Timur');
    }

    public function test_only_coordinators_can_view_the_recap_and_empty_database_is_handled(): void
    {
        $coordinator = $this->createUser('coordinator', 'coordinator@example.test');

        $this->actingAs($coordinator)
            ->get(route('coordinator.reports.recap'))
            ->assertOk()
            ->assertSee('Belum ada laporan')
            ->assertViewHas('summary', fn ($summary): bool => (int) $summary->total === 0);

        foreach (['reporter', 'volunteer'] as $role) {
            $user = $this->createUser($role, $role.'@example.test');

            $this->actingAs($user)
                ->get(route('coordinator.reports.recap'))
                ->assertForbidden();
        }

        auth()->logout();
        $this->get(route('coordinator.reports.recap'))
            ->assertRedirect(route('login'));
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
        string $status,
        string $city,
        string $province,
    ): Report {
        $address = Address::create([
            'detail_address' => 'Alamat test rekap',
            'city_regency' => $city,
            'province' => $province,
            'island' => 'Kalimantan',
        ]);

        return $reporter->reports()->create([
            'report_description' => $description,
            'incident_time' => now(),
            'condition_level' => 'high',
            'address_id' => $address->address_id,
            'status' => $status,
        ]);
    }
}

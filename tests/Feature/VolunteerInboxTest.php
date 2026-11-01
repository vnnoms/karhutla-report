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

class VolunteerInboxTest extends TestCase
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

    public function test_volunteer_sees_pending_reports_and_not_processed_reports(): void
    {
        $volunteer = $this->createUser('volunteer', 'volunteer@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $this->createReport($reporter, 'Laporan api yang menunggu', 'pending');
        $this->createReport($reporter, 'Laporan yang sudah ditinjau', 'reviewed');

        $this->actingAs($volunteer)
            ->get(route('dashboard.volunteer'))
            ->assertOk()
            ->assertSee('1 laporan')
            ->assertSee('Laporan api yang menunggu')
            ->assertSee('Samarinda, Kalimantan Timur')
            ->assertDontSee('Laporan yang sudah ditinjau');
    }

    public function test_volunteer_dashboard_shows_an_empty_state_and_rejects_other_roles(): void
    {
        $volunteer = $this->createUser('volunteer', 'volunteer@example.test');

        $this->actingAs($volunteer)
            ->get(route('dashboard.volunteer'))
            ->assertOk()
            ->assertSee('Belum ada laporan baru')
            ->assertSee('0 laporan');

        $reporter = $this->createUser('reporter', 'reporter@example.test');
        $this->actingAs($reporter)
            ->get(route('dashboard.volunteer'))
            ->assertForbidden();
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

    private function createReport(User $reporter, string $description, string $status): Report
    {
        $address = Address::create([
            'detail_address' => 'Alamat laporan volunteer',
            'city_regency' => 'Samarinda',
            'province' => 'Kalimantan Timur',
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

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserManagementTest extends TestCase
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
    }

    public function test_coordinator_can_list_and_view_user_details(): void
    {
        $admin = $this->createUser('coordinator', 'admin@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');

        $this->actingAs($admin)
            ->get(route('coordinator.users.index'))
            ->assertOk()
            ->assertSee($reporter->email);

        $this->get(route('coordinator.users.show', $reporter))
            ->assertOk()
            ->assertSee($reporter->email)
            ->assertSee('Ubah role');
    }

    public function test_coordinator_can_change_roles_between_reporter_and_volunteer(): void
    {
        $admin = $this->createUser('coordinator', 'admin@example.test');
        $reporter = $this->createUser('reporter', 'reporter@example.test');

        $this->actingAs($admin)
            ->put(route('coordinator.users.role.update', $reporter), ['role' => 'volunteer'])
            ->assertRedirect(route('coordinator.users.show', $reporter));
        $this->assertDatabaseHas('users', ['user_id' => $reporter->user_id, 'role' => 'volunteer']);

        $this->put(route('coordinator.users.role.update', $reporter), ['role' => 'reporter'])
            ->assertRedirect(route('coordinator.users.show', $reporter));
        $this->assertDatabaseHas('users', ['user_id' => $reporter->user_id, 'role' => 'reporter']);
    }

    public function test_coordinator_cannot_change_their_own_or_another_coordinator_role(): void
    {
        $admin = $this->createUser('coordinator', 'admin@example.test');
        $otherAdmin = $this->createUser('coordinator', 'other-admin@example.test');

        $this->actingAs($admin)
            ->get(route('coordinator.users.role.edit', $admin))
            ->assertForbidden();
        $this->put(route('coordinator.users.role.update', $admin), ['role' => 'volunteer'])
            ->assertForbidden();
        $this->get(route('coordinator.users.role.edit', $otherAdmin))
            ->assertForbidden();
        $this->put(route('coordinator.users.role.update', $otherAdmin), ['role' => 'reporter'])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['user_id' => $admin->user_id, 'role' => 'coordinator']);
        $this->assertDatabaseHas('users', ['user_id' => $otherAdmin->user_id, 'role' => 'coordinator']);
    }

    public function test_reporters_and_volunteers_cannot_access_user_management(): void
    {
        foreach (['reporter', 'volunteer'] as $role) {
            $actor = $this->createUser($role, $role.'@example.test');
            $target = $this->createUser('reporter', 'target-'.$role.'@example.test');

            $this->actingAs($actor)
                ->get(route('coordinator.users.index'))
                ->assertForbidden();
            $this->get(route('coordinator.users.show', $target))->assertForbidden();
            $this->put(route('coordinator.users.role.update', $target), ['role' => 'volunteer'])
                ->assertForbidden();
        }
    }

    public function test_invalid_roles_are_rejected_and_coordinator_role_cannot_be_assigned(): void
    {
        $admin = $this->createUser('coordinator', 'admin@example.test');
        $target = $this->createUser('reporter', 'reporter@example.test');

        $this->actingAs($admin)
            ->put(route('coordinator.users.role.update', $target), ['role' => 'unknown'])
            ->assertSessionHasErrors('role');
        $this->put(route('coordinator.users.role.update', $target), ['role' => 'coordinator'])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['user_id' => $target->user_id, 'role' => 'reporter']);
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
}

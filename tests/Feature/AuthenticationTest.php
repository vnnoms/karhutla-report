<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
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

    public function test_login_and_registration_pages_are_available_to_guests(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    public function test_registration_persists_a_reporter_with_a_hashed_password(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Rani Lestari',
            'email' => 'rani@example.test',
            'phone_number' => '081234567890',
            'password' => 'HutanAman123!',
            'password_confirmation' => 'HutanAman123!',
            'role' => 'coordinator',
        ]);

        $user = User::query()->where('email', 'rani@example.test')->firstOrFail();

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'role' => 'reporter',
        ]);
        $this->assertNotSame('HutanAman123!', $user->password);
        $this->assertTrue(Hash::check('HutanAman123!', $user->password));
    }

    public function test_registration_rejects_an_existing_email(): void
    {
        User::create([
            'name' => 'Rani Lestari',
            'email' => 'rani@example.test',
            'phone_number' => '081234567890',
            'password' => Hash::make('HutanAman123!'),
            'role' => 'reporter',
        ]);

        $this->post(route('register.store'), [
            'name' => 'Rani Lestari',
            'email' => 'rani@example.test',
            'phone_number' => '081234567890',
            'password' => 'HutanAman123!',
            'password_confirmation' => 'HutanAman123!',
        ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_rejects_invalid_credentials_with_a_clear_message(): void
    {
        $this->createUser('reporter');

        $this->post(route('login.store'), [
            'email' => 'rani@example.test',
            'password' => 'WrongPassword123!',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors([
                'email' => 'Email atau password salah.',
            ]);

        $this->assertGuest();
    }

    public function test_login_authenticates_with_the_custom_user_primary_key_and_regenerates_session(): void
    {
        $user = $this->createUser('reporter');
        $this->get(route('login'));
        $originalSessionId = $this->app['session.store']->getId();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'HutanAman123!',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($originalSessionId, $this->app['session.store']->getId());
    }

    public function test_logout_clears_authentication_session_data_and_csrf_token(): void
    {
        $user = $this->createUser('reporter');
        $this->actingAs($user)->withSession([
            'sentinel' => 'remove-me',
            '_token' => 'old-csrf-token',
        ]);

        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionMissing('sentinel');

        $this->assertGuest();
        $this->assertNotSame('old-csrf-token', $this->app['session.store']->token());
    }

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    #[DataProvider('dashboardRedirectCases')]
    public function test_dashboard_redirects_each_role_to_its_own_page(string $role): void
    {
        $this->actingAs($this->createUser($role))
            ->get(route('dashboard'))
            ->assertRedirect(route("dashboard.{$role}"));
    }

    #[DataProvider('dashboardRoleCases')]
    public function test_each_dashboard_only_allows_its_matching_role(
        string $userRole,
        string $dashboardRole,
        bool $isAllowed,
    ): void {
        $user = $this->createUser($userRole);

        $response = $this->actingAs($user)->get(route("dashboard.{$dashboardRole}"));

        if ($isAllowed) {
            $response->assertOk();
        } else {
            $response->assertForbidden();
        }
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function dashboardRoleCases(): array
    {
        $roles = ['reporter', 'volunteer', 'coordinator'];
        $cases = [];

        foreach ($roles as $userRole) {
            foreach ($roles as $dashboardRole) {
                $cases["{$userRole}_{$dashboardRole}"] = [
                    $userRole,
                    $dashboardRole,
                    $userRole === $dashboardRole,
                ];
            }
        }

        return $cases;
    }

    /**
     * @return array<string, array{string}>
     */
    public static function dashboardRedirectCases(): array
    {
        return [
            'reporter' => ['reporter'],
            'volunteer' => ['volunteer'],
            'coordinator' => ['coordinator'],
        ];
    }

    private function createUser(string $role): User
    {
        return User::create([
            'name' => 'Rani Lestari',
            'email' => 'rani@example.test',
            'phone_number' => '081234567890',
            'password' => Hash::make('HutanAman123!'),
            'role' => $role,
        ]);
    }
}

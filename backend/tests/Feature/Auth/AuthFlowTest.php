<?php

namespace Tests\Feature\Auth;

use App\Domains\Auth\Enums\RoleName;
use App\Domains\Auth\Events\UserRegistered;
use App\Domains\Auth\Listeners\SendWelcomeEmail;
use App\Domains\Auth\Models\RefreshToken;
use App\Domains\Auth\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'password1';

    public function test_register_creates_user_with_default_role_and_dispatches_event(): void
    {
        Event::fake([UserRegistered::class]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.roles.0.name', RoleName::User->value);

        Event::assertDispatched(UserRegistered::class);
    }

    public function test_register_validation_errors_use_standard_contract(): void
    {
        $this->postJson('/api/v1/auth/register', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null)
            ->assertJsonStructure(['errors' => ['name', 'email', 'password']]);
    }

    public function test_login_returns_access_token_and_refresh_cookie(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertOk()
            ->assertJsonStructure(['data' => ['access_token', 'token_type', 'user']])
            ->assertJsonMissingPath('data.refresh_token')
            ->assertCookie('refresh_token');
    }

    public function test_login_with_wrong_password_is_unauthorized(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_deactivated_account_cannot_login(): void
    {
        $user = User::factory()->inactive()->create(['password' => self::PASSWORD]);

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertForbidden();
    }

    public function test_me_requires_a_token_and_returns_user(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        $user = User::factory()->create(['password' => self::PASSWORD]);
        $token = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => self::PASSWORD])
            ->json('data.access_token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_refresh_rotates_token_and_reuse_revokes_family(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);

        $login = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        $first = $login->getCookie('refresh_token', false)->getValue();

        $refresh = $this->call('POST', '/api/v1/auth/refresh', cookies: ['refresh_token' => $first], server: ['HTTP_ACCEPT' => 'application/json']);
        $refresh->assertOk();
        $second = $refresh->getCookie('refresh_token', false)->getValue();
        $this->assertNotSame($first, $second);

        // Replaying the already-rotated token => theft detected, whole family revoked.
        $this->call('POST', '/api/v1/auth/refresh', cookies: ['refresh_token' => $first], server: ['HTTP_ACCEPT' => 'application/json'])
            ->assertUnauthorized();

        $this->assertSame(0, RefreshToken::query()->whereNull('revoked_at')->count());
    }

    public function test_refresh_without_cookie_is_unauthorized(): void
    {
        $this->postJson('/api/v1/auth/refresh')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'No refresh token provided');
    }

    public function test_logout_revokes_refresh_token(): void
    {
        $user = User::factory()->create(['password' => self::PASSWORD]);
        $login = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        $token = $login->json('data.access_token');
        $refresh = $login->getCookie('refresh_token', false)->getValue();

        $this->call('POST', '/api/v1/auth/logout', cookies: ['refresh_token' => $refresh], server: ['HTTP_ACCEPT' => 'application/json', 'HTTP_AUTHORIZATION' => "Bearer {$token}"])
            ->assertOk();

        $this->assertSame(0, RefreshToken::query()->whereNull('revoked_at')->count());
    }

    public function test_welcome_listener_is_queued(): void
    {
        $this->assertContains(ShouldQueue::class, class_implements(SendWelcomeEmail::class));
    }
}

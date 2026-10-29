<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function mockGoogleReturning(string $email): void
    {
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getEmail')->andReturn($email);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('userFromToken')->with('tok')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_existing_user_logs_in_via_google(): void
    {
        $account = Account::factory()->create();
        User::factory()->create(['account_id' => $account->id, 'email' => 'staff@example.com']);

        $this->mockGoogleReturning('staff@example.com');

        $this->postJson('/api/auth/google', ['access_token' => 'tok'])
            ->assertOk()
            ->assertJsonPath('data.email', 'staff@example.com');
    }

    public function test_unknown_google_email_is_forbidden(): void
    {
        $this->mockGoogleReturning('stranger@example.com');

        $this->postJson('/api/auth/google', ['access_token' => 'tok'])
            ->assertStatus(403);
    }
}

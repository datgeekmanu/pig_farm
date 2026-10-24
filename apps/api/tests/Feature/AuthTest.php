<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farmA;

    private Farm $farmB;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create([
            'account_id' => $account->id,
            'email' => 'owner@example.com',
        ]);
        $this->farmA = Farm::factory()->create(['account_id' => $account->id, 'name' => 'Farm A']);
        $this->farmB = Farm::factory()->create(['account_id' => $account->id, 'name' => 'Farm B']);
        $this->user->farms()->attach($this->farmA, ['role' => 'owner']);
        $this->user->farms()->attach($this->farmB, ['role' => 'staff']);
    }

    // An Origin on a stateful domain makes the request run the session stack.
    private function statefulHeaders(): void
    {
        $this->withHeaders(['Origin' => 'http://localhost:3000']);
    }

    public function test_login_establishes_session_and_grants_access(): void
    {
        $this->statefulHeaders();

        $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('data.email', 'owner@example.com')
            ->assertJsonMissingPath('token'); // cookie session, no bearer token

        // session cookie carried by the test client
        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonCount(2, 'farms');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->statefulHeaders();

        $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'wrong',
        ])->assertStatus(422);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_user_farms_and_active_farm(): void
    {
        $this->actingAs($this->user)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'owner@example.com')
            ->assertJsonCount(2, 'farms')
            ->assertJsonPath('active_farm_id', $this->farmA->id);
    }

    public function test_x_farm_id_header_selects_farm(): void
    {
        $this->actingAs($this->user)
            ->withHeader('X-Farm-Id', (string) $this->farmB->id)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('active_farm_id', $this->farmB->id);
    }

    public function test_x_farm_id_for_foreign_farm_is_forbidden(): void
    {
        $foreign = Farm::factory()->create();

        $this->actingAs($this->user)
            ->withHeader('X-Farm-Id', (string) $foreign->id)
            ->getJson('/api/me')
            ->assertStatus(403);
    }

    public function test_logout_ends_session(): void
    {
        $this->statefulHeaders();

        $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->postJson('/api/logout')->assertOk();
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/me')->assertStatus(401);
    }
}

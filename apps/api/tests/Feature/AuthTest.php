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

    private function token(): string
    {
        return $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'password',
        ])->json('token');
    }

    public function test_login_returns_token_and_user(): void
    {
        $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'email'], 'token', 'token_type'])
            ->assertJsonPath('data.email', 'owner@example.com');
    }

    public function test_login_fails_with_wrong_password(): void
    {
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
        $this->withToken($this->token())->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'owner@example.com')
            ->assertJsonCount(2, 'farms')
            ->assertJsonPath('active_farm_id', $this->farmA->id); // first farm by default
    }

    public function test_x_farm_id_header_selects_farm(): void
    {
        $this->withToken($this->token())
            ->withHeader('X-Farm-Id', (string) $this->farmB->id)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('active_farm_id', $this->farmB->id);
    }

    public function test_x_farm_id_for_foreign_farm_is_forbidden(): void
    {
        $foreign = Farm::factory()->create(); // different account, user not a member

        $this->withToken($this->token())
            ->withHeader('X-Farm-Id', (string) $foreign->id)
            ->getJson('/api/me')
            ->assertStatus(403);
    }

    public function test_logout_revokes_current_token(): void
    {
        $token = $this->token();

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        // Fresh request = fresh guard resolution (the auth singleton caches the
        // resolved user across sub-requests within one test).
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/me')->assertStatus(401);
    }
}

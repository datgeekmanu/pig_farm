<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    private User $user;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $this->account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $this->account->id]);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
    }

    public function test_change_password(): void
    {
        $this->actingAs($this->user)->putJson('/api/account/password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        $this->assertTrue(Hash::check('newpassword123', $this->user->fresh()->password));
    }

    public function test_change_password_rejects_wrong_current(): void
    {
        $this->actingAs($this->user)->putJson('/api/account/password', [
            'current_password' => 'wrong',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(422);
    }

    public function test_owner_can_delete_account(): void
    {
        $this->actingAs($this->user)->deleteJson('/api/account', [
            'password' => 'password',
            'confirmation' => 'SUPPRIMER',
        ])->assertOk();

        $this->assertDatabaseMissing('accounts', ['id' => $this->account->id]);
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    public function test_delete_requires_exact_confirmation_word(): void
    {
        $this->actingAs($this->user)->deleteJson('/api/account', [
            'password' => 'password',
            'confirmation' => 'delete',
        ])->assertStatus(422);

        $this->assertDatabaseHas('accounts', ['id' => $this->account->id]);
    }

    public function test_staff_cannot_delete_account(): void
    {
        $account = Account::factory()->create();
        $staff = User::factory()->create(['account_id' => $account->id]);
        $farm = Farm::factory()->create(['account_id' => $account->id]);
        $staff->farms()->attach($farm, ['role' => 'staff']);

        $this->actingAs($staff)->deleteJson('/api/account', [
            'password' => 'password',
            'confirmation' => 'SUPPRIMER',
        ])->assertStatus(403);

        $this->assertDatabaseHas('accounts', ['id' => $account->id]);
    }
}

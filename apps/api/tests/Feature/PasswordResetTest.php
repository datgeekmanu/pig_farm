<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create([
            'account_id' => $account->id,
            'email' => 'staff@example.com',
        ]);
    }

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $this->postJson('/api/forgot-password', ['email' => 'staff@example.com'])->assertOk();

        Notification::assertSentTo($this->user, ResetPassword::class);
    }

    public function test_reset_password_with_valid_token(): void
    {
        Notification::fake();
        $this->postJson('/api/forgot-password', ['email' => 'staff@example.com'])->assertOk();

        $token = null;
        Notification::assertSentTo($this->user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'staff@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        $this->assertTrue(Hash::check('newpassword123', $this->user->fresh()->password));
    }

    public function test_reset_password_rejects_bad_token(): void
    {
        $this->postJson('/api/reset-password', [
            'token' => 'bogus',
            'email' => 'staff@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(422);
    }
}

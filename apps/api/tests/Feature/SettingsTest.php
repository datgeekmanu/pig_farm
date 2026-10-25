<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    private User $owner;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->owner = User::factory()->create(['account_id' => $this->account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $this->account->id]);
        $this->owner->farms()->attach($this->farm, ['role' => 'owner']);
    }

    public function test_show_and_update_settings(): void
    {
        $this->actingAs($this->owner)->getJson('/api/farm')
            ->assertOk()->assertJsonPath('data.id', $this->farm->id);

        $this->actingAs($this->owner)->putJson('/api/farm', [
            'phone' => '+22507000000', 'seuil_rentabilite' => 100000, 'receipt_color' => '#2980B9',
        ])->assertOk()
            ->assertJsonPath('data.phone', '+22507000000')
            ->assertJsonPath('data.seuil_rentabilite', 100000);
    }

    public function test_staff_cannot_update_settings(): void
    {
        $staff = User::factory()->create(['account_id' => $this->account->id]);
        $staff->farms()->attach($this->farm, ['role' => 'staff']);

        $this->actingAs($staff)->putJson('/api/farm', ['phone' => 'x'])->assertStatus(403);
    }

    public function test_upload_logo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->owner)->post('/api/farm/logo', [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.logo_url', fn ($u) => $u !== null);

        Storage::disk('public')->assertExists("farms/{$this->farm->id}/logo/".basename($this->farm->fresh()->logo_path));
    }

    public function test_notification_prefs(): void
    {
        $this->actingAs($this->owner)->putJson('/api/farm/notifications', [
            'prefs' => ['vaccins' => true, 'stock_bas' => false],
        ])->assertOk()->assertJsonPath('data.vaccins', true);

        $this->actingAs($this->owner)->getJson('/api/farm/notifications')
            ->assertOk()->assertJsonPath('data.stock_bas', false);
    }

    public function test_tutorials_list(): void
    {
        $this->actingAs($this->owner)->getJson('/api/tutorials')
            ->assertOk()->assertJsonCount(6, 'data');
    }
}

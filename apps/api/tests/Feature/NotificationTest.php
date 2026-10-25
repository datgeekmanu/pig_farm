<?php

namespace Tests\Feature;

use App\Mail\NotificationDigest;
use App\Models\Account;
use App\Models\Breeding;
use App\Models\Farm;
use App\Models\FarmNotification;
use App\Models\Pig;
use App\Models\StockItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id]);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
    }

    private function seedAlerts(): void
    {
        StockItem::factory()->create(['farm_id' => $this->farm->id, 'quantite' => 2, 'seuil_alerte' => 5]);
        $sow = Pig::factory()->create(['farm_id' => $this->farm->id, 'categorie' => 'truie']);
        Breeding::factory()->create([
            'farm_id' => $this->farm->id, 'sow_id' => $sow->id, 'status' => 'gestante',
            'bred_on' => now()->subDays(112)->toDateString(),
            'expected_farrow_on' => now()->addDay()->toDateString(),
        ]);
    }

    public function test_generate_is_idempotent(): void
    {
        Mail::fake();
        $this->seedAlerts();

        $this->artisan('notifications:generate')->assertSuccessful();
        $count = FarmNotification::withoutGlobalScopes()->count();
        $this->assertSame(2, $count); // stock_bas + mise_bas_prevue

        $this->artisan('notifications:generate')->assertSuccessful();
        $this->assertSame(2, FarmNotification::withoutGlobalScopes()->count()); // no duplicates
    }

    public function test_generate_emails_owner_when_enabled(): void
    {
        Mail::fake();
        $this->seedAlerts();

        $this->artisan('notifications:generate')->assertSuccessful();

        Mail::assertSent(NotificationDigest::class);
    }

    public function test_generate_skips_email_when_disabled(): void
    {
        Mail::fake();
        $this->farm->update(['notification_prefs' => ['email' => false]]);
        $this->seedAlerts();

        $this->artisan('notifications:generate')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_index_lists_and_counts_unread(): void
    {
        FarmNotification::factory()->count(2)->create(['farm_id' => $this->farm->id]);
        FarmNotification::factory()->create(['farm_id' => $this->farm->id, 'read_at' => now()]);

        $res = $this->actingAs($this->user)->getJson('/api/notifications')->assertOk();

        $this->assertSame(3, count($res->json('data')));
        $this->assertSame(2, $res->json('unread_count'));
    }

    public function test_mark_read_and_read_all(): void
    {
        $n = FarmNotification::factory()->create(['farm_id' => $this->farm->id]);
        FarmNotification::factory()->count(2)->create(['farm_id' => $this->farm->id]);

        $this->actingAs($this->user)->patchJson("/api/notifications/{$n->id}/read")->assertOk();
        $this->assertNotNull($n->fresh()->read_at);

        $this->actingAs($this->user)->postJson('/api/notifications/read-all')->assertOk();
        $this->assertSame(0, FarmNotification::withoutGlobalScopes()->whereNull('read_at')->count());
    }

    public function test_cannot_read_foreign_notification(): void
    {
        $foreign = FarmNotification::factory()->create();

        $this->actingAs($this->user)->patchJson("/api/notifications/{$foreign->id}/read")->assertStatus(404);
    }
}

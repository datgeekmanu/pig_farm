<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\MortalityRecord;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingTest extends TestCase
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

    public function test_create_listing_available_from_lot_head(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id, 'effectif_initial' => 30]);
        MortalityRecord::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $batch->id, 'count' => 5]);

        $res = $this->actingAs($this->user)->postJson('/api/listings', [
            'batch_id' => $batch->id, 'title' => 'Porcs charcutiers', 'category' => 'Charcutier',
            'priceXofPerKg' => 1800, 'marketXofPerKg' => 2000, 'published' => true,
        ])->assertStatus(201);

        $res->assertJsonPath('data.lotId', $batch->id)
            ->assertJsonPath('data.available', 25) // 30 - 5 deaths
            ->assertJsonPath('data.priceXofPerKg', 1800)
            ->assertJsonPath('data.published', true);
    }

    public function test_toggle_visibility(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id]);
        $id = $this->actingAs($this->user)->postJson('/api/listings', [
            'batch_id' => $batch->id, 'title' => 'Lot A', 'priceXofPerKg' => 1500, 'visible' => true,
        ])->json('data.id');

        $this->actingAs($this->user)->patchJson("/api/listings/{$id}", ['title' => 'Lot A', 'priceXofPerKg' => 1500, 'visible' => false])
            ->assertOk()->assertJsonPath('data.visible', false);
    }

    public function test_cannot_edit_foreign_listing(): void
    {
        $foreign = Product::factory()->create();

        $this->actingAs($this->user)->patchJson("/api/listings/{$foreign->id}", ['title' => 'x', 'priceXofPerKg' => 1])
            ->assertStatus(404);
    }
}

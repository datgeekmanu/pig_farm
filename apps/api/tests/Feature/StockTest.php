<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Farm;
use App\Models\StockItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTest extends TestCase
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

    public function test_create_stock_item(): void
    {
        $this->actingAs($this->user)->postJson('/api/stock-items', [
            'type' => 'alimentation', 'nom' => 'Maïs', 'quantite' => 100, 'unite' => 'kg', 'seuil_alerte' => 20,
        ])->assertStatus(201)->assertJsonPath('data.nom', 'Maïs')->assertJsonPath('data.is_low', false);
    }

    public function test_index_filters_by_type_and_low(): void
    {
        StockItem::factory()->create(['farm_id' => $this->farm->id, 'type' => 'alimentation', 'quantite' => 100, 'seuil_alerte' => 20]);
        StockItem::factory()->create(['farm_id' => $this->farm->id, 'type' => 'veterinaire', 'quantite' => 2, 'seuil_alerte' => 5]);

        $this->actingAs($this->user)->getJson('/api/stock-items?type=veterinaire')
            ->assertOk()->assertJsonCount(1, 'data');

        $this->actingAs($this->user)->getJson('/api/stock-items?low=1')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.is_low', true);
    }

    public function test_movement_adjusts_quantity(): void
    {
        $item = StockItem::factory()->create(['farm_id' => $this->farm->id, 'quantite' => 10]);

        $this->actingAs($this->user)->postJson("/api/stock-items/{$item->id}/movements", [
            'change' => -3, 'reason' => 'perte',
        ])->assertStatus(201);

        $this->assertEquals(7, $item->fresh()->quantite);
        $this->assertDatabaseHas('stock_movements', ['stock_item_id' => $item->id, 'change' => -3]);
    }

    public function test_movement_cannot_go_negative(): void
    {
        $item = StockItem::factory()->create(['farm_id' => $this->farm->id, 'quantite' => 2]);

        $this->actingAs($this->user)->postJson("/api/stock-items/{$item->id}/movements", ['change' => -5])
            ->assertStatus(422);

        $this->assertEquals(2, $item->fresh()->quantite);
    }

    public function test_cannot_access_foreign_stock_item(): void
    {
        $foreign = StockItem::factory()->create();

        $this->actingAs($this->user)->getJson("/api/stock-items/{$foreign->id}")->assertStatus(404);
    }
}

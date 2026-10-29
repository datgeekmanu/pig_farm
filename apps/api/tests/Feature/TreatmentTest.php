<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Pig;
use App\Models\StockItem;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    private Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id]);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
        $this->batch = Batch::factory()->create(['farm_id' => $this->farm->id]);
    }

    public function test_lot_treatment_requires_batch(): void
    {
        $this->actingAs($this->user)->postJson('/api/treatments', [
            'scope' => 'lot', 'date' => '2026-03-01', 'produit' => 'Ivermectine',
        ])->assertStatus(422)->assertJsonValidationErrors('batch_id');
    }

    public function test_individual_treatment_requires_pig(): void
    {
        $this->actingAs($this->user)->postJson('/api/treatments', [
            'scope' => 'individual', 'date' => '2026-03-01', 'produit' => 'Vaccin',
        ])->assertStatus(422)->assertJsonValidationErrors('pig_id');
    }

    public function test_create_lot_treatment(): void
    {
        $this->actingAs($this->user)->postJson('/api/treatments', [
            'scope' => 'lot', 'batch_id' => $this->batch->id,
            'date' => '2026-03-01', 'produit' => 'Ivermectine', 'dose' => '1ml/10kg', 'cout' => 5000,
        ])->assertStatus(201)->assertJsonPath('data.produit', 'Ivermectine');
    }

    public function test_treatment_consumes_linked_stock(): void
    {
        $pig = Pig::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $this->batch->id]);
        $vet = StockItem::factory()->create(['farm_id' => $this->farm->id, 'type' => 'veterinaire', 'quantite' => 10]);

        $this->actingAs($this->user)->postJson('/api/treatments', [
            'scope' => 'individual', 'pig_id' => $pig->id,
            'date' => '2026-03-01', 'produit' => 'Amoxicilline',
            'stock_item_id' => $vet->id, 'quantite_utilisee' => 4,
        ])->assertStatus(201);

        $this->assertEquals(6, $vet->fresh()->quantite);
        $this->assertDatabaseHas('stock_movements', ['stock_item_id' => $vet->id, 'change' => -4, 'reason' => 'traitement']);
    }

    public function test_treatment_rejected_when_stock_insufficient(): void
    {
        $pig = Pig::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $this->batch->id]);
        $vet = StockItem::factory()->create(['farm_id' => $this->farm->id, 'type' => 'veterinaire', 'quantite' => 2]);

        $this->actingAs($this->user)->postJson('/api/treatments', [
            'scope' => 'individual', 'pig_id' => $pig->id,
            'date' => '2026-03-01', 'produit' => 'Amoxicilline',
            'stock_item_id' => $vet->id, 'quantite_utilisee' => 5,
        ])->assertStatus(422);

        $this->assertEquals(2, $vet->fresh()->quantite);
        $this->assertDatabaseCount('treatments', 0); // rolled back
    }

    public function test_index_filters_by_scope(): void
    {
        Treatment::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $this->batch->id, 'scope' => 'lot']);
        Treatment::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $this->batch->id, 'scope' => 'prophylaxie']);

        $this->actingAs($this->user)->getJson('/api/treatments?scope=prophylaxie')
            ->assertOk()->assertJsonCount(1, 'data');
    }
}

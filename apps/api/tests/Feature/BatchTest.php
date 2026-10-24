<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchTest extends TestCase
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

    public function test_index_lists_only_active_farm_batches(): void
    {
        Batch::factory()->count(2)->create(['farm_id' => $this->farm->id]);
        Batch::factory()->create(); // another farm

        $this->actingAs($this->user)->getJson('/api/batches')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_store_creates_batch_in_active_farm(): void
    {
        $this->actingAs($this->user)->postJson('/api/batches', [
            'nom' => 'Lot 2026-A',
            'date_mise_en_lot' => '2026-10-01',
            'effectif_initial' => 20,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.nom', 'Lot 2026-A');

        $this->assertDatabaseHas('batches', [
            'nom' => 'Lot 2026-A',
            'farm_id' => $this->farm->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->user)->postJson('/api/batches', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nom', 'date_mise_en_lot', 'effectif_initial']);
    }

    public function test_show_update_and_delete(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id]);

        $this->actingAs($this->user)->getJson("/api/batches/{$batch->id}")
            ->assertOk()->assertJsonPath('data.id', $batch->id);

        $this->actingAs($this->user)->putJson("/api/batches/{$batch->id}", ['nom' => 'Renamed'])
            ->assertOk()->assertJsonPath('data.nom', 'Renamed');

        $this->actingAs($this->user)->deleteJson("/api/batches/{$batch->id}")->assertOk();
        $this->assertDatabaseMissing('batches', ['id' => $batch->id]);
    }

    public function test_cannot_access_batch_from_another_farm(): void
    {
        $foreign = Batch::factory()->create(); // different account/farm

        $this->actingAs($this->user)->getJson("/api/batches/{$foreign->id}")
            ->assertStatus(404);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Pig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PigTest extends TestCase
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

    public function test_store_creates_pig(): void
    {
        $this->actingAs($this->user)->postJson('/api/pigs', [
            'identifiant' => 'P001',
            'poids_arrivee_kg' => 25.5,
            'sexe' => 'male',
            'categorie' => 'engraissement',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.identifiant', 'P001');

        $this->assertDatabaseHas('pigs', ['identifiant' => 'P001', 'farm_id' => $this->farm->id]);
    }

    public function test_store_requires_identifiant_and_weight(): void
    {
        $this->actingAs($this->user)->postJson('/api/pigs', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['identifiant', 'poids_arrivee_kg']);
    }

    public function test_identifiant_is_unique_per_farm_only(): void
    {
        Pig::factory()->create(['farm_id' => $this->farm->id, 'identifiant' => 'P001']);

        // same identifiant in this farm -> rejected
        $this->actingAs($this->user)->postJson('/api/pigs', [
            'identifiant' => 'P001', 'poids_arrivee_kg' => 20,
        ])->assertJsonValidationErrors('identifiant');

        // same identifiant exists in another farm -> allowed here
        Pig::factory()->create(['identifiant' => 'P999']);
        $this->actingAs($this->user)->postJson('/api/pigs', [
            'identifiant' => 'P999', 'poids_arrivee_kg' => 20,
        ])->assertStatus(201);
    }

    public function test_parent_and_batch_must_belong_to_same_farm(): void
    {
        $foreignPig = Pig::factory()->create();
        $foreignBatch = Batch::factory()->create();

        $this->actingAs($this->user)->postJson('/api/pigs', [
            'identifiant' => 'P010',
            'poids_arrivee_kg' => 22,
            'pere_id' => $foreignPig->id,
            'batch_id' => $foreignBatch->id,
        ])->assertJsonValidationErrors(['pere_id', 'batch_id']);
    }

    public function test_links_parent_in_same_farm(): void
    {
        $sire = Pig::factory()->create(['farm_id' => $this->farm->id, 'categorie' => 'verrat']);

        $this->actingAs($this->user)->postJson('/api/pigs', [
            'identifiant' => 'P011',
            'poids_arrivee_kg' => 22,
            'pere_id' => $sire->id,
        ])->assertStatus(201)->assertJsonPath('data.pere_id', $sire->id);
    }

    public function test_show_update_delete_and_isolation(): void
    {
        $pig = Pig::factory()->create(['farm_id' => $this->farm->id]);

        $this->actingAs($this->user)->getJson("/api/pigs/{$pig->id}")
            ->assertOk()->assertJsonPath('data.id', $pig->id);

        $this->actingAs($this->user)->putJson("/api/pigs/{$pig->id}", ['nom' => 'Hercule'])
            ->assertOk()->assertJsonPath('data.nom', 'Hercule');

        $this->actingAs($this->user)->deleteJson("/api/pigs/{$pig->id}")->assertOk();
        $this->assertDatabaseMissing('pigs', ['id' => $pig->id]);

        $foreign = Pig::factory()->create();
        $this->actingAs($this->user)->getJson("/api/pigs/{$foreign->id}")->assertStatus(404);
    }
}

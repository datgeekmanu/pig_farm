<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\Pig;
use App\Models\Salary;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id, 'seuil_rentabilite' => 80000]);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
    }

    public function test_sale_computes_line_totals_and_sujets(): void
    {
        $res = $this->actingAs($this->user)->postJson('/api/sales', [
            'date_vente' => '2026-03-10',
            'items' => [
                ['quantite' => 5, 'poids_total_kg' => 100, 'prix_kg' => 1800],
                ['quantite' => 2, 'poids_total_kg' => 50, 'prix_kg' => 2000],
            ],
        ])->assertStatus(201);

        $res->assertJsonPath('data.total', 280000) // 100*1800 + 50*2000
            ->assertJsonPath('data.sujets_vendus', 7);
    }

    public function test_sale_applies_discount_then_tax(): void
    {
        $res = $this->actingAs($this->user)->postJson('/api/sales', [
            'date_vente' => '2026-03-10',
            'items' => [
                ['quantite' => 1, 'poids_total_kg' => 100, 'prix_kg' => 1000, 'remise_pct' => 10, 'taxe_pct' => 5],
            ],
        ])->assertStatus(201);

        // 100000 * 0.9 * 1.05 = 94500
        $res->assertJsonPath('data.total', 94500);
    }

    public function test_sale_marks_pig_sold_and_reduces_head(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id, 'effectif_initial' => 10]);
        $pig = Pig::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $batch->id]);

        $this->actingAs($this->user)->postJson('/api/sales', [
            'date_vente' => '2026-03-10',
            'items' => [['pig_id' => $pig->id, 'batch_id' => $batch->id, 'quantite' => 1, 'poids_total_kg' => 90, 'prix_kg' => 1500]],
        ])->assertStatus(201);

        $this->assertSame('sold', $pig->fresh()->status);
        $this->assertSame(9, $batch->fresh()->currentHead()); // 10 - 1 sold
    }

    public function test_expense_crud_and_filter(): void
    {
        $this->actingAs($this->user)->postJson('/api/expenses', [
            'date' => '2026-03-05', 'montant' => 40000, 'categorie' => 'Alimentation',
        ])->assertStatus(201);

        $this->actingAs($this->user)->getJson('/api/expenses?categorie=Alimentation')
            ->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_salary_requires_valid_month(): void
    {
        $this->actingAs($this->user)->postJson('/api/salaries', [
            'nom_ouvrier' => 'Kossi', 'montant' => 50000, 'date_paiement' => '2026-03-31', 'mois_concerne' => 'mars',
        ])->assertStatus(422)->assertJsonValidationErrors('mois_concerne');
    }

    public function test_finance_overview(): void
    {
        Sale::factory()->create(['farm_id' => $this->farm->id, 'date_vente' => '2026-03-10', 'total' => 100000]);
        Expense::factory()->create(['farm_id' => $this->farm->id, 'date' => '2026-03-05', 'montant' => 40000, 'categorie' => 'Alimentation']);
        Salary::factory()->create(['farm_id' => $this->farm->id, 'date_paiement' => '2026-03-31', 'montant' => 50000, 'mois_concerne' => '2026-03']);

        $res = $this->actingAs($this->user)->getJson('/api/finances/overview')->assertOk();

        $this->assertSame(100000, $res->json('data.chiffre_affaires'));
        $this->assertSame(90000, $res->json('data.depenses_totales'));
        $this->assertSame(10000, $res->json('data.benefice_brut'));
        $this->assertSame(80000, $res->json('data.seuil_rentabilite'));

        $cats = collect($res->json('data.par_categorie'))->pluck('montant', 'categorie');
        $this->assertSame(40000, $cats['Alimentation']);
        $this->assertSame(50000, $cats['Salaire']);
    }

    public function test_cannot_access_foreign_sale(): void
    {
        $foreign = Sale::factory()->create();

        $this->actingAs($this->user)->getJson("/api/sales/{$foreign->id}")->assertStatus(404);
    }
}

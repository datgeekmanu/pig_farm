<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Farm;
use App\Models\Ingredient;
use App\Models\Nutrient;
use App\Models\User;
use Database\Seeders\NutrientSeeder;
use Database\Seeders\PhaseRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormulationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NutrientSeeder::class);
        $this->seed(PhaseRequirementSeeder::class);

        $account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id]);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
    }

    private function ingredient(string $nom, int $prix, array $map): Ingredient
    {
        $ing = Ingredient::factory()->create(['farm_id' => $this->farm->id, 'nom' => $nom, 'prix_kg' => $prix]);
        $ids = Nutrient::pluck('id', 'code');
        $ing->nutrients()->attach(
            collect($map)->mapWithKeys(fn ($v, $code) => [$ids[$code] => ['value' => $v]])->all()
        );

        return $ing;
    }

    private function seedLibrary(): array
    {
        return [
            'mais' => $this->ingredient('Maïs', 250, ['em' => 3300, 'mat' => 8.5, 'lysine' => 0.25, 'calcium' => 0.02, 'phosphore' => 0.28, 'cellulose' => 2.2]),
            'soja' => $this->ingredient('Tourteau de soja', 400, ['em' => 2300, 'mat' => 44, 'lysine' => 2.8, 'calcium' => 0.30, 'phosphore' => 0.65, 'cellulose' => 7]),
            'poisson' => $this->ingredient('Farine de poisson', 700, ['em' => 2800, 'mat' => 60, 'lysine' => 4.8, 'calcium' => 4, 'phosphore' => 2.8, 'cellulose' => 1]),
            'huitre' => $this->ingredient("Coquille d'huître", 100, ['calcium' => 38]),
            'phosphate' => $this->ingredient('Phosphate bicalcique', 500, ['calcium' => 24, 'phosphore' => 18]),
            'son' => $this->ingredient('Son de blé', 150, ['em' => 1300, 'mat' => 15, 'lysine' => 0.6, 'calcium' => 0.12, 'phosphore' => 1.0, 'cellulose' => 10]),
        ];
    }

    public function test_create_ingredient_with_nutrients(): void
    {
        $em = Nutrient::where('code', 'em')->first();

        $this->actingAs($this->user)->postJson('/api/ingredients', [
            'nom' => 'Maïs', 'prix_kg' => 250,
            'nutrients' => [['nutrient_id' => $em->id, 'value' => 3300]],
        ])->assertStatus(201)->assertJsonPath('data.nom', 'Maïs');

        $this->assertDatabaseHas('ingredient_nutrient', ['nutrient_id' => $em->id, 'value' => 3300]);
    }

    public function test_manual_formula_requires_100_percent(): void
    {
        $lib = $this->seedLibrary();

        $this->actingAs($this->user)->postJson('/api/formulas', [
            'nom' => 'Test', 'phase' => 'croissance',
            'lines' => [['ingredient_id' => $lib['mais']->id, 'percent' => 90]],
        ])->assertStatus(422)->assertJsonValidationErrors('lines');
    }

    public function test_manual_formula_computes_cost_and_nutrition(): void
    {
        $lib = $this->seedLibrary();

        $res = $this->actingAs($this->user)->postJson('/api/formulas', [
            'nom' => 'Croissance 50/50', 'phase' => 'croissance',
            'lines' => [
                ['ingredient_id' => $lib['mais']->id, 'percent' => 50],
                ['ingredient_id' => $lib['soja']->id, 'percent' => 50],
            ],
        ])->assertStatus(201);

        // cost = .5*250 + .5*400 = 325 ; mat = .5*8.5 + .5*44 = 26.25
        $res->assertJsonPath('data.cost_per_kg', 325);
        $this->assertEqualsWithDelta(26.25, $res->json('nutrition.mat'), 0.01);
    }

    public function test_analyze_flags_deficiency(): void
    {
        $lib = $this->seedLibrary();

        // 100% maïs for croissance -> protein (mat>=16) deficient
        $res = $this->actingAs($this->user)->postJson('/api/formulas/analyze', [
            'phase' => 'croissance',
            'lines' => [['ingredient_id' => $lib['mais']->id, 'percent' => 100]],
        ])->assertOk();

        $mat = collect($res->json('analysis'))->firstWhere('nutrient', 'mat');
        $this->assertSame('low', $mat['status']);
    }

    public function test_optimize_finds_feasible_least_cost_mix(): void
    {
        $this->seedLibrary();

        $res = $this->actingAs($this->user)->postJson('/api/formulas/optimize', [
            'phase' => 'croissance', 'nom' => 'Éco', 'save' => true,
        ])->assertOk()->assertJsonPath('status', 'optimal');

        $sum = collect($res->json('lines'))->sum('percent');
        $this->assertEqualsWithDelta(100, $sum, 1.0);
        $this->assertGreaterThan(0, $res->json('cost_per_kg'));

        // every constrained nutrient should be satisfied
        $low = collect($res->json('analysis'))->where('status', 'low');
        $this->assertCount(0, $low);

        // saved as an LP formula
        $this->assertDatabaseHas('formulas', ['nom' => 'Éco', 'mode' => 'lp']);
    }

    public function test_optimize_infeasible_returns_422(): void
    {
        // only maïs (mat 8.5) but sevrage needs mat >= 20 -> infeasible
        $this->ingredient('Maïs', 250, ['em' => 3300, 'mat' => 8.5, 'lysine' => 0.25, 'calcium' => 0.02, 'phosphore' => 0.28]);

        $this->actingAs($this->user)->postJson('/api/formulas/optimize', ['phase' => 'sevrage'])
            ->assertStatus(422)
            ->assertJsonPath('status', 'infeasible');
    }
}

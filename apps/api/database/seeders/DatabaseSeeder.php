<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Farm;
use App\Models\Ingredient;
use App\Models\Nutrient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  use WithoutModelEvents;

  public function run(): void
  {
    $this->call([NutrientSeeder::class, PhaseRequirementSeeder::class]);

    $account = Account::create(['name' => 'Ferme de John Doe']);

    $owner = User::factory()->create([
      'account_id' => $account->id,
      'name' => 'John Doe',
      'email' => 'owner@example.com',
    ]);

    $farms = collect(['Ferme Principale', 'Ferme Secondaire'])->map(
      fn(string $name) => Farm::create([
        'account_id' => $account->id,
        'name' => $name,
        'currency' => 'XOF',
      ])
    );

    foreach ($farms as $farm) {
      $owner->farms()->attach($farm->id, ['role' => 'owner']);
    }

    $this->seedIngredients($farms->first());
  }

  // Common West-African pig-feed ingredients with approximate nutrient profiles.
  // ponytail: placeholder nutrient/price values — refine per real lab analyses / market.
  private function seedIngredients(Farm $farm): void
  {
    $nutrientIds = Nutrient::pluck('id', 'code');

    // nom, prix_kg (FCFA), [em, mat, lysine, calcium, phosphore, cellulose]
    $ingredients = [
      ['Maïs', 250, [3300, 8.5, 0.25, 0.02, 0.28, 2.2]],
      ['Tourteau de soja', 400, [2300, 44, 2.80, 0.30, 0.65, 7.0]],
      ['Son de blé', 150, [1300, 15, 0.60, 0.12, 1.00, 10.0]],
      ['Farine de poisson', 700, [2800, 60, 4.80, 4.00, 2.80, 1.0]],
      ["Coquille d'huître", 100, [0, 0, 0, 38.0, 0, 0]],
      ['Phosphate bicalcique', 500, [0, 0, 0, 24.0, 18.0, 0]],
    ];
    $codes = ['em', 'mat', 'lysine', 'calcium', 'phosphore', 'cellulose'];

    foreach ($ingredients as [$nom, $prix, $values]) {
      $ing = new Ingredient(['nom' => $nom, 'prix_kg' => $prix]);
      $ing->farm_id = $farm->id;
      $ing->save();

      $ing->nutrients()->attach(
        collect($codes)->mapWithKeys(fn($code, $i) => [
          $nutrientIds[$code] => ['value' => $values[$i]],
        ])->all()
      );
    }
  }
}

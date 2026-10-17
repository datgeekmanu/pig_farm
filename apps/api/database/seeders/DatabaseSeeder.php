<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $account = Account::create(['name' => 'Ferme de John Doe']);

        $owner = User::factory()->create([
            'account_id' => $account->id,
            'name' => 'John Doe',
            'email' => 'owner@example.com',
        ]);

        $farms = collect(['Ferme Principale', 'Ferme Secondaire'])->map(
            fn (string $name) => Farm::create([
                'account_id' => $account->id,
                'name' => $name,
                'currency' => 'XOF',
            ])
        );

        foreach ($farms as $farm) {
            $owner->farms()->attach($farm->id, ['role' => 'owner']);
        }
    }
}

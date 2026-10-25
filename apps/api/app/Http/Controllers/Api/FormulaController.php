<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Formula\AnalyzeFormulaRequest;
use App\Http\Requests\Formula\OptimizeFormulaRequest;
use App\Http\Requests\Formula\StoreFormulaRequest;
use App\Http\Resources\FormulaResource;
use App\Models\Formula;
use App\Models\Ingredient;
use App\Support\FeedOptimizer;
use App\Support\FormulaCalculator;
use App\Support\Simplex;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class FormulaController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return FormulaResource::collection(
            Formula::with('lines.ingredient')->latest()->paginate(20)
        );
    }

    public function store(StoreFormulaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $calc = $this->calcLines($data['lines']);
        $nutrition = FormulaCalculator::nutrition($calc);

        $formula = DB::transaction(function () use ($data, $calc) {
            $formula = Formula::create([
                'nom' => $data['nom'],
                'phase' => $data['phase'] ?? null,
                'mode' => 'manual',
                'poids_total_kg' => $data['poids_total_kg'] ?? null,
                'cost_per_kg' => FormulaCalculator::costPerKg($calc),
            ]);
            foreach ($data['lines'] as $line) {
                $formula->lines()->create($line);
            }

            return $formula;
        });

        return FormulaResource::make($formula->load('lines.ingredient'))
            ->additional([
                'nutrition' => $nutrition,
                'analysis' => $data['phase'] ?? null ? FormulaCalculator::analyze($data['phase'], $nutrition) : [],
            ])
            ->response()->setStatusCode(201);
    }

    public function show(Formula $formula): FormulaResource
    {
        $formula->load('lines.ingredient');
        $calc = $formula->lines->map(fn ($l) => ['ingredient' => $l->ingredient, 'percent' => (float) $l->percent])->all();
        $nutrition = FormulaCalculator::nutrition($calc);

        return FormulaResource::make($formula)->additional([
            'nutrition' => $nutrition,
            'analysis' => $formula->phase ? FormulaCalculator::analyze($formula->phase, $nutrition) : [],
        ]);
    }

    public function update(StoreFormulaRequest $request, Formula $formula): FormulaResource
    {
        $data = $request->validated();
        $calc = $this->calcLines($data['lines']);

        DB::transaction(function () use ($formula, $data, $calc) {
            $formula->update([
                'nom' => $data['nom'],
                'phase' => $data['phase'] ?? null,
                'poids_total_kg' => $data['poids_total_kg'] ?? null,
                'cost_per_kg' => FormulaCalculator::costPerKg($calc),
            ]);
            $formula->lines()->delete();
            foreach ($data['lines'] as $line) {
                $formula->lines()->create($line);
            }
        });

        return FormulaResource::make($formula->load('lines.ingredient'));
    }

    public function destroy(Formula $formula): JsonResponse
    {
        $formula->delete();

        return response()->json(['message' => 'Formula deleted.']);
    }

    public function analyze(AnalyzeFormulaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $calc = $this->calcLines($data['lines']);
        $nutrition = FormulaCalculator::nutrition($calc);

        return response()->json([
            'cost_per_kg' => FormulaCalculator::costPerKg($calc),
            'nutrition' => $nutrition,
            'analysis' => FormulaCalculator::analyze($data['phase'], $nutrition),
        ]);
    }

    public function optimize(OptimizeFormulaRequest $request): JsonResponse
    {
        $data = $request->validated();

        $ingredients = Ingredient::with('nutrients')
            ->when(! empty($data['ingredient_ids']), fn ($q) => $q->whereIn('id', $data['ingredient_ids']))
            ->get();

        $result = FeedOptimizer::optimize($ingredients, $data['phase']);

        if ($result['status'] !== Simplex::OPTIMAL) {
            return response()->json([
                'message' => $result['status'] === Simplex::INFEASIBLE
                    ? 'Aucune formule ne satisfait ces contraintes avec ces ingrédients.'
                    : 'Problème non borné.',
                'status' => $result['status'],
            ], 422);
        }

        $saved = null;
        if ($request->boolean('save')) {
            $saved = DB::transaction(function () use ($data, $result) {
                $formula = Formula::create([
                    'nom' => $data['nom'] ?? 'Formule économique',
                    'phase' => $data['phase'],
                    'mode' => 'lp',
                    'poids_total_kg' => $data['poids_total_kg'] ?? null,
                    'cost_per_kg' => $result['cost_per_kg'],
                ]);
                foreach ($result['lines'] as $line) {
                    $formula->lines()->create($line);
                }

                return $formula->load('lines.ingredient');
            });
        }

        return response()->json([
            'status' => $result['status'],
            'lines' => $result['lines'],
            'cost_per_kg' => $result['cost_per_kg'],
            'nutrition' => $result['nutrition'],
            'analysis' => $result['analysis'],
            'formula' => $saved ? FormulaResource::make($saved) : null,
        ]);
    }

    /**
     * Load the farm's ingredients for the given lines into calculator input.
     *
     * @param  array<int, array{ingredient_id:int,percent:float|string}>  $lines
     * @return list<array{ingredient:Ingredient,percent:float}>
     */
    private function calcLines(array $lines): array
    {
        $ingredients = Ingredient::with('nutrients')
            ->whereIn('id', collect($lines)->pluck('ingredient_id'))
            ->get()->keyBy('id');

        return collect($lines)->map(fn ($line) => [
            'ingredient' => $ingredients[$line['ingredient_id']],
            'percent' => (float) $line['percent'],
        ])->all();
    }
}

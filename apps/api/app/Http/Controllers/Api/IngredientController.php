<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ingredient\StoreIngredientRequest;
use App\Http\Requests\Ingredient\UpdateIngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IngredientController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return IngredientResource::collection(Ingredient::with('nutrients')->get());
    }

    public function store(StoreIngredientRequest $request): JsonResponse
    {
        $ingredient = Ingredient::create($request->safe()->only(['nom', 'prix_kg']));
        $this->syncNutrients($ingredient, $request->validated('nutrients', []));

        return IngredientResource::make($ingredient->load('nutrients'))->response()->setStatusCode(201);
    }

    public function show(Ingredient $ingredient): IngredientResource
    {
        return IngredientResource::make($ingredient->load('nutrients'));
    }

    public function update(UpdateIngredientRequest $request, Ingredient $ingredient): IngredientResource
    {
        $ingredient->update($request->safe()->only(['nom', 'prix_kg']));

        if ($request->has('nutrients')) {
            $this->syncNutrients($ingredient, $request->validated('nutrients', []));
        }

        return IngredientResource::make($ingredient->load('nutrients'));
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        $ingredient->delete();

        return response()->json(['message' => 'Ingredient deleted.']);
    }

    /**
     * @param  array<int, array{nutrient_id:int,value:float}>  $nutrients
     */
    private function syncNutrients(Ingredient $ingredient, array $nutrients): void
    {
        $ingredient->nutrients()->sync(
            collect($nutrients)->mapWithKeys(fn ($n) => [$n['nutrient_id'] => ['value' => $n['value']]])->all()
        );
    }
}

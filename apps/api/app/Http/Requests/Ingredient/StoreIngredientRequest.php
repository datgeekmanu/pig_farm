<?php

namespace App\Http\Requests\Ingredient;

use Illuminate\Foundation\Http\FormRequest;

class StoreIngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prix_kg' => ['required', 'integer', 'min:0'],
            'nutrients' => ['array'],
            'nutrients.*.nutrient_id' => ['required', 'exists:nutrients,id'],
            'nutrients.*.value' => ['required', 'numeric', 'min:0'],
        ];
    }
}

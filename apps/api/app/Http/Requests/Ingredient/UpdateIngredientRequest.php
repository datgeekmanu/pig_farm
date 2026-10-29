<?php

namespace App\Http\Requests\Ingredient;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIngredientRequest extends FormRequest
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
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'prix_kg' => ['sometimes', 'required', 'integer', 'min:0'],
            'nutrients' => ['sometimes', 'array'],
            'nutrients.*.nutrient_id' => ['required', 'exists:nutrients,id'],
            'nutrients.*.value' => ['required', 'numeric', 'min:0'],
        ];
    }
}

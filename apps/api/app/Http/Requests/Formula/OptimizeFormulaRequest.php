<?php

namespace App\Http\Requests\Formula;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OptimizeFormulaRequest extends FormRequest
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
        $farmId = app(CurrentFarm::class)->id();

        return [
            'phase' => ['required', Rule::in(StoreFormulaRequest::PHASES)],
            'nom' => ['nullable', 'string', 'max:255'],
            'ingredient_ids' => ['nullable', 'array', 'min:1'],
            'ingredient_ids.*' => [Rule::exists('ingredients', 'id')->where('farm_id', $farmId)],
            'poids_total_kg' => ['nullable', 'numeric', 'min:0'],
            'save' => ['boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\Formula;

use App\Support\CurrentFarm;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFormulaRequest extends FormRequest
{
    public const PHASES = ['sevrage', 'demarrage', 'croissance', 'finition'];

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
            'nom' => ['required', 'string', 'max:255'],
            'phase' => ['nullable', Rule::in(self::PHASES)],
            'poids_total_kg' => ['nullable', 'numeric', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.ingredient_id' => ['required', Rule::exists('ingredients', 'id')->where('farm_id', $farmId)],
            'lines.*.percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $sum = collect($this->input('lines', []))->sum('percent');
            if (abs($sum - 100) > 0.5) {
                $validator->errors()->add('lines', "Les pourcentages doivent totaliser 100% (actuel: {$sum}%).");
            }
        });
    }
}

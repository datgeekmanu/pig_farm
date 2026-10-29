<?php

namespace App\Http\Requests\Lot;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

// A lot weigh-in can be given as raw sample weights, an explicit average, or a
// total + count. At least one must resolve to an average.
class StoreLotWeighingRequest extends FormRequest
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
            'weighed_on' => ['nullable', 'date'],
            'count' => ['nullable', 'integer', 'min:1'],
            'total_kg' => ['nullable', 'numeric', 'min:0'],
            'avg_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'weights' => ['nullable', 'array', 'min:1'],
            'weights.*' => ['numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasWeights = ! empty($this->input('weights'));
            $hasAvg = $this->filled('avg_weight_kg');
            $hasTotal = $this->filled('count') && $this->filled('total_kg');

            if (! $hasWeights && ! $hasAvg && ! $hasTotal) {
                $validator->errors()->add('avg_weight_kg', 'Fournir des poids, une moyenne, ou un total + effectif.');
            }
        });
    }
}

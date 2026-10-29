<?php

namespace App\Http\Requests\Mortality;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMortalityRequest extends FormRequest
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
            // a death is tied to an individual pig OR a batch loss (at least one)
            'pig_id' => ['nullable', 'required_without:batch_id', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'batch_id' => ['nullable', 'required_without:pig_id', Rule::exists('batches', 'id')->where('farm_id', $farmId)],
            'died_on' => ['required', 'date'],
            'cause' => ['nullable', 'string', 'max:255'],
            'count' => ['nullable', 'integer', 'min:1'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

<?php

namespace App\Http\Requests\Weaning;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWeaningRequest extends FormRequest
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
            'farrowing_id' => ['required', Rule::exists('farrowings', 'id')->where('farm_id', $farmId)],
            'weaned_on' => ['required', 'date'],
            'weaned_count' => ['required', 'integer', 'min:0'],
            'avg_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

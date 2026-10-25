<?php

namespace App\Http\Requests\Breeding;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBreedingRequest extends FormRequest
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
            'sow_id' => ['required', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'boar_id' => ['nullable', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'band_id' => ['nullable', Rule::exists('bands', 'id')->where('farm_id', $farmId)],
            'bred_on' => ['required', 'date'],
            'method' => ['nullable', Rule::in(['naturelle', 'insemination'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}

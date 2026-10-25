<?php

namespace App\Http\Requests\Breeding;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBreedingRequest extends FormRequest
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
            'boar_id' => ['nullable', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'band_id' => ['nullable', Rule::exists('bands', 'id')->where('farm_id', $farmId)],
            'bred_on' => ['sometimes', 'required', 'date'],
            'method' => ['nullable', Rule::in(['naturelle', 'insemination'])],
            'ultrasound_on' => ['nullable', 'date'],
            'ultrasound_result' => ['nullable', Rule::in(['gestante', 'vide'])],
            'heat_return_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

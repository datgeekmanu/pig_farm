<?php

namespace App\Http\Requests\Farrowing;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFarrowingRequest extends FormRequest
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
            'breeding_id' => ['nullable', Rule::exists('breedings', 'id')->where('farm_id', $farmId)],
            // sow derived from breeding when breeding_id is given, else required
            'sow_id' => ['required_without:breeding_id', 'nullable', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'farrowed_on' => ['required', 'date'],
            'born_alive' => ['required', 'integer', 'min:0'],
            'born_dead' => ['nullable', 'integer', 'min:0'],
            'mummies' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

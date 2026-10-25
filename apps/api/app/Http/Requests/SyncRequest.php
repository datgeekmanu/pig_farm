<?php

namespace App\Http\Requests;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncRequest extends FormRequest
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
            'weights' => ['array'],
            'weights.*.client_uuid' => ['required', 'uuid'],
            'weights.*.pig_id' => ['required', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'weights.*.weight_kg' => ['required', 'numeric', 'min:0'],
            'weights.*.weighed_on' => ['nullable', 'date'],
            'weights.*.notes' => ['nullable', 'string'],

            'mortalities' => ['array'],
            'mortalities.*.client_uuid' => ['required', 'uuid'],
            'mortalities.*.pig_id' => ['nullable', 'required_without:mortalities.*.batch_id', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'mortalities.*.batch_id' => ['nullable', 'required_without:mortalities.*.pig_id', Rule::exists('batches', 'id')->where('farm_id', $farmId)],
            'mortalities.*.died_on' => ['required', 'date'],
            'mortalities.*.cause' => ['nullable', 'string'],
            'mortalities.*.count' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

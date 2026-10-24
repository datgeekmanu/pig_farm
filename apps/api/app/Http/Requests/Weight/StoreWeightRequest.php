<?php

namespace App\Http\Requests\Weight;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeightRequest extends FormRequest
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
            'weight_kg' => ['required', 'numeric', 'min:0', 'max:1000'],
            'weighed_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

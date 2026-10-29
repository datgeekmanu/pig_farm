<?php

namespace App\Http\Requests\Farrowing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFarrowingRequest extends FormRequest
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
            'farrowed_on' => ['sometimes', 'required', 'date'],
            'born_alive' => ['sometimes', 'required', 'integer', 'min:0'],
            'born_dead' => ['nullable', 'integer', 'min:0'],
            'mummies' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

<?php

namespace App\Http\Requests\Farm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFarmRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'currency' => ['sometimes', 'required', Rule::in(['XOF', 'EUR'])],
            'seuil_rentabilite' => ['nullable', 'integer', 'min:0'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string', 'max:255'],
            'payment_info' => ['nullable', 'string'],
            'receipt_color' => ['nullable', 'string', 'max:20'],
        ];
    }
}

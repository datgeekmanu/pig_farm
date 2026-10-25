<?php

namespace App\Http\Requests\Band;

use Illuminate\Foundation\Http\FormRequest;

class StoreBandRequest extends FormRequest
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
            'nom' => ['required', 'string', 'max:255'],
            'taille_objectif' => ['nullable', 'integer', 'min:0'],
            'date_prevue_saillie' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

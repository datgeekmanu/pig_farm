<?php

namespace App\Http\Requests\Treatment;

use Illuminate\Foundation\Http\FormRequest;

// Edits metadata only. Scope/pig/batch and stock consumption are fixed at creation
// (changing them would require unwinding the stock movement) — recreate instead.
class UpdateTreatmentRequest extends FormRequest
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
            'date' => ['sometimes', 'required', 'date'],
            'produit' => ['sometimes', 'required', 'string', 'max:255'],
            'dose' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'motif' => ['nullable', 'string', 'max:255'],
            'soignant' => ['nullable', 'string', 'max:255'],
            'soignant_tel' => ['nullable', 'string', 'max:50'],
            'cout' => ['nullable', 'integer', 'min:0'],
            'date_rappel' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

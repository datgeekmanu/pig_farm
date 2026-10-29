<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockItemRequest extends FormRequest
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
            'type' => ['sometimes', 'required', Rule::in(['alimentation', 'veterinaire'])],
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:255'],
            'unite' => ['nullable', 'string', 'max:50'],
            'seuil_alerte' => ['nullable', 'numeric', 'min:0'],
            'prix_unitaire' => ['nullable', 'integer', 'min:0'],
            'date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            // quantite is changed via stock movements, not direct edits
        ];
    }
}

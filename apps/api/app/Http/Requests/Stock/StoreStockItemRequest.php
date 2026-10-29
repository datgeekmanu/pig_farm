<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockItemRequest extends FormRequest
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
            'type' => ['required', Rule::in(['alimentation', 'veterinaire'])],
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:255'],
            'quantite' => ['nullable', 'numeric', 'min:0'],
            'unite' => ['nullable', 'string', 'max:50'],
            'seuil_alerte' => ['nullable', 'numeric', 'min:0'],
            'prix_unitaire' => ['nullable', 'integer', 'min:0'],
            'date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

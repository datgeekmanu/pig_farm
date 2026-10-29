<?php

namespace App\Http\Requests\Batch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBatchRequest extends FormRequest
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
            'batiment_loge' => ['nullable', 'string', 'max:255'],
            'date_mise_en_lot' => ['required', 'date'],
            'effectif_initial' => ['required', 'integer', 'min:1'],
            'date_naissance' => ['nullable', 'date'],
            'poids_moyen_initial_kg' => ['nullable', 'numeric', 'min:0'],
            'prix_achat_unitaire' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['active', 'sold', 'closed'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}

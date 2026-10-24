<?php

namespace App\Http\Requests\Pig;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePigRequest extends FormRequest
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
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('farm_id', $farmId)],
            'identifiant' => [
                'required', 'string', 'max:255',
                Rule::unique('pigs')->where('farm_id', $farmId),
            ],
            'nom' => ['nullable', 'string', 'max:255'],
            'sexe' => ['nullable', Rule::in(['male', 'female'])],
            'categorie' => ['nullable', Rule::in(['verrat', 'truie', 'engraissement', 'porcelet'])],
            'date_naissance' => ['nullable', 'date'],
            'date_arrivee' => ['nullable', 'date'],
            'date_inconnue' => ['boolean'],
            'poids_arrivee_kg' => ['required', 'numeric', 'min:0'],
            'prix_achat' => ['nullable', 'integer', 'min:0'],
            'valeur_reforme' => ['nullable', 'integer', 'min:0'],
            'provenance' => ['nullable', 'string', 'max:255'],
            'pere_id' => ['nullable', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'mere_id' => ['nullable', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'pere_nom' => ['nullable', 'string', 'max:255'],
            'mere_nom' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['active', 'sold', 'dead'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}

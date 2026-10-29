<?php

namespace App\Http\Requests\Sale;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public const MODES = ['especes', 'mobile_money', 'virement', 'cheque'];

    public const STATUSES = ['paye', 'partiel', 'impaye'];

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
            'date_vente' => ['required', 'date'],
            'acheteur' => ['nullable', 'string', 'max:255'],
            'acheteur_tel' => ['nullable', 'string', 'max:50'],
            'acheteur_email' => ['nullable', 'email'],
            'mode_paiement' => ['nullable', Rule::in(self::MODES)],
            'statut_paiement' => ['nullable', Rule::in(self::STATUSES)],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.batch_id' => ['nullable', Rule::exists('batches', 'id')->where('farm_id', $farmId)],
            'items.*.pig_id' => ['nullable', Rule::exists('pigs', 'id')->where('farm_id', $farmId)],
            'items.*.type_porc' => ['nullable', 'string', 'max:255'],
            'items.*.quantite' => ['required', 'integer', 'min:1'],
            'items.*.poids_total_kg' => ['nullable', 'numeric', 'min:0'],
            'items.*.prix_kg' => ['nullable', 'integer', 'min:0'],
            'items.*.age_mois' => ['nullable', 'integer', 'min:0'],
            'items.*.remise_pct' => ['nullable', 'numeric', 'between:0,100'],
            'items.*.taxe_pct' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}

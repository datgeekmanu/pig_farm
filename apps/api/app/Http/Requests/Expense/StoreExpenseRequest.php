<?php

namespace App\Http\Requests\Expense;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public const CATEGORIES = [
        'Alimentation', 'Sanitaire / Vétérinaire', 'Vétérinaire', 'Achat animaux',
        'Achat porcelets', 'Équipement', 'Main d\'œuvre', 'Énergie (eau, électricité)',
        'Transport', 'Salaire', 'Autre',
    ];

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
            'date' => ['required', 'date'],
            'montant' => ['required', 'integer', 'min:0'],
            'categorie' => ['required', Rule::in(self::CATEGORIES)],
            'description' => ['nullable', 'string', 'max:255'],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('farm_id', $farmId)],
            'notes' => ['nullable', 'string'],
        ];
    }
}

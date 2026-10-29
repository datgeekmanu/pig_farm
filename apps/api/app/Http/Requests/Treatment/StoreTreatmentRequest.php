<?php

namespace App\Http\Requests\Treatment;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTreatmentRequest extends FormRequest
{
    public const SCOPES = ['lot', 'individual', 'prophylaxie'];

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
        $scope = $this->input('scope');

        return [
            'scope' => ['required', Rule::in(self::SCOPES)],
            // individual needs a pig; lot/prophylaxie need a batch
            'pig_id' => [
                Rule::requiredIf($scope === 'individual'), 'nullable',
                Rule::exists('pigs', 'id')->where('farm_id', $farmId),
            ],
            'batch_id' => [
                Rule::requiredIf(in_array($scope, ['lot', 'prophylaxie'], true)), 'nullable',
                Rule::exists('batches', 'id')->where('farm_id', $farmId),
            ],
            'date' => ['required', 'date'],
            'produit' => ['required', 'string', 'max:255'],
            'dose' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'motif' => ['nullable', 'string', 'max:255'],
            'soignant' => ['nullable', 'string', 'max:255'],
            'soignant_tel' => ['nullable', 'string', 'max:50'],
            'cout' => ['nullable', 'integer', 'min:0'],
            'date_rappel' => ['nullable', 'date'],
            // optional link to a veterinary stock item to decrement
            'stock_item_id' => ['nullable', Rule::exists('stock_items', 'id')->where('farm_id', $farmId)],
            'quantite_utilisee' => ['nullable', 'numeric', 'min:0', 'required_with:stock_item_id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

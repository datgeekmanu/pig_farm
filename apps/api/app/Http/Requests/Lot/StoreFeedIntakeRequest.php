<?php

namespace App\Http\Requests\Lot;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedIntakeRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'quantity_kg' => ['required', 'numeric', 'min:0.01'],
            'stock_item_id' => ['nullable', Rule::exists('stock_items', 'id')->where('farm_id', $farmId)],
            'cost' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

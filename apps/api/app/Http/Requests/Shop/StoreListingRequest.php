<?php

namespace App\Http\Requests\Shop;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'priceXofPerKg' => ['required', 'integer', 'min:0'],
            'marketXofPerKg' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'visible' => ['boolean'],
            'published' => ['boolean'],
        ];
    }
}

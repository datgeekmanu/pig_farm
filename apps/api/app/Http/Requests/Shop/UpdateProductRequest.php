<?php

namespace App\Http\Requests\Shop;

use App\Support\CurrentFarm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'type' => ['sometimes', 'required', Rule::in(['live_pig', 'pork_cut'])],
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['sometimes', 'required', Rule::in(['tete', 'kg'])],
            'prix' => ['sometimes', 'required', 'integer', 'min:0'],
            'stock_qty' => ['sometimes', 'required', 'numeric', 'min:0'],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('farm_id', $farmId)],
            'is_published' => ['boolean'],
        ];
    }
}

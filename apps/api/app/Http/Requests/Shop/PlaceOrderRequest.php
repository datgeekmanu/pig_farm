<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'delivery_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ];
    }
}

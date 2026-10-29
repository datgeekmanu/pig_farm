<?php

namespace App\Http\Requests\Farm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPrefsRequest extends FormRequest
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
            'prefs' => ['required', 'array'],
            'prefs.*' => ['boolean'],
        ];
    }
}
